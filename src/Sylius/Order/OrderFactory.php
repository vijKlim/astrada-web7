<?php

namespace App\Sylius\Order;

use App\DataType\TsRange;
use App\Entity\Delivery;
use App\Entity\LocalBusiness;
use App\Sylius\Customer\CustomerInterface;
use AppBundle\Sylius\Product\ProductVariantFactory;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Taxation\Calculator\CalculatorInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Webmozart\Assert\Assert;

class OrderFactory implements FactoryInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var FactoryInterface $orderItemFactory
     */
    private $orderItemFactory;

    /**
     * @var ProductVariantFactoryInterface $productVariantFactory
     */
    private $productVariantFactory;

    /**
     * @var OrderItemQuantityModifierInterface $orderItemQuantityModifier
     */
    private $orderItemQuantityModifier;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(
        FactoryInterface $factory,
        ChannelContextInterface $channelContext,
        FactoryInterface $orderItemFactory,
        ProductVariantFactoryInterface $productVariantFactory,
        OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        OrderModifierInterface $orderModifier)
    {
        $this->factory = $factory;
        $this->channelContext = $channelContext;
        $this->orderItemFactory = $orderItemFactory;
        $this->productVariantFactory = $productVariantFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderModifier = $orderModifier;
    }

    /**
     * {@inheritdoc}
     */
    public function createNew()
    {
        $order = $this->factory->createNew();
        $order->setChannel($this->channelContext->getChannel());

        return $order;
    }

    public function createForBusiness(LocalBusiness $business)
    {
        $order = $this->createNew();
        $order->setBusiness($business);

        if (!$business->isFulfillmentMethodEnabled('delivery') && $business->isFulfillmentMethodEnabled('collection')) {
            $order->setTakeaway(true);
        }

        return $order;
    }
}