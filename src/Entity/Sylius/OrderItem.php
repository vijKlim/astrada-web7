<?php


namespace App\Entity\Sylius;

use App\Sylius\Product\ProductVariantInterface;
use Sylius\Component\Order\Model\OrderItem as BaseOrderItem;
use Sylius\Component\Order\Model\OrderItemInterface as BaseOrderItemInterface;

class OrderItem extends BaseOrderItem
{
    /**
     * @var ProductVariantInterface
     */
    protected $variant;

    /**
     * {@inheritdoc}
     */
    public function getVariant(): ?ProductVariantInterface
    {
        return $this->variant;
    }

    /**
     * {@inheritdoc}
     */
    public function setVariant(?ProductVariantInterface $variant): void
    {
        $this->variant = $variant;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(BaseOrderItemInterface $item): bool
    {
        return parent::equals($item) || ($item instanceof static && $item->getVariant() === $this->variant);
    }
}