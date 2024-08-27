<?php

namespace Mgodev\Webhook\Model;

use Mgodev\Webhook\Api\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Product implements ProductInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Get SKU by product ID.
     *
     * @param int $productId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSkuById($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            return $product->getSku();
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Product with ID "%1" does not exist.', $productId));
        }
    }
}
