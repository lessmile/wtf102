<?php

namespace Mgodev\Webhook\Api;

interface ProductInterface
{
    /**
     * Get SKU by product ID.
     *
     * @param int $productId
     * @return string
     */
    public function getSkuById($productId);
}
