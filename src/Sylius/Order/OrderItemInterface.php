<?php


namespace App\Sylius\Order;

use App\Sylius\Product\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderItemInterface as BaseOrderItemInterface;

interface OrderItemInterface extends BaseOrderItemInterface
{
    /**
     * @return ProductVariantInterface|null
     */
    public function getVariant(): ?ProductVariantInterface;

    /**
     * @param ProductVariantInterface|null $variant
     */
    public function setVariant(?ProductVariantInterface $variant): void;
}