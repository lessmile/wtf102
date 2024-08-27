<?php
namespace Mgodev\Webhook\Plugin;

use Magento\Sales\Model\Order;
use Magento\Catalog\Model\Product;
use Magento\Framework\HTTP\Client\Curl;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableType;
use Magento\Bundle\Model\ResourceModel\Selection as BundleSelection;

class OrderAndProductWebhookPlugin
{
    protected $curl;
    protected $configurableType;
    protected $bundleSelection;

    public function __construct(
        Curl $curl,
        ConfigurableType $configurableType,
        BundleSelection $bundleSelection
    ) {
        $this->curl = $curl;
        $this->configurableType = $configurableType;
        $this->bundleSelection = $bundleSelection;
    }

    public function afterSave($subject, $result)
    {
        $webhookUrl = 'https://enkil31xdetyg.x.pipedream.net/magento/dev/webhooks';

        if ($subject instanceof Order) {
            if ($subject->getIsObjectNew() || $subject->hasDataChanges()) {
                $data = [
                    'type' => 'order',
                    'order_id' => $subject->getId(),
                    'status' => $subject->getStatus()
                ];
                $this->curl->post($webhookUrl, json_encode($data));
            }
        }

        if ($subject instanceof Product) {
            if ($subject->getIsObjectNew() || $subject->hasDataChanges()) {
                $productType = $subject->getTypeId();
                $isParentProduct = false;

                if ($productType === 'configurable' || $productType === 'grouped' || $productType === 'bundle') {
                    $isParentProduct = true;
                } elseif ($productType === 'simple') {
                    $isChildOfConfigurable = $this->isChildOfConfigurable($subject);
                    $isChildOfBundle = $this->isChildOfBundle($subject);
                    $isParentProduct = !$isChildOfConfigurable && !$isChildOfBundle;
                }

                $data = [
                    'type' => 'product',
                    'product_id' => $subject->getId(),
                    'sku' => $subject->getSku(),
                    'product_type' => $productType,
                    'is_parent_product' => $isParentProduct
                ];

                $this->curl->post($webhookUrl, json_encode($data));
            }
        }

        return $result;
    }

    protected function isChildOfConfigurable(Product $product)
    {
        $parentIds = $this->configurableType->getParentIdsByChild($product->getId());
        return !empty($parentIds);
    }

    protected function isChildOfBundle(Product $product)
    {
        $parentIds = $this->bundleSelection->getParentIdsByChild($product->getId());
        return !empty($parentIds);
    }
}
