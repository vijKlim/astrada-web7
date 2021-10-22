<?php

namespace Astrada\SubscriptionBundle\Strategy;



use Astrada\SubscriptionBundle\Model\SubscriptionInterface;
use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use Astrada\SubscriptionBundle\Exception\PermanentSubscriptionException;

interface SubscriptionStrategyInterface
{
    /**
     * @param SubscriptionProductInterface        $product       Product that will be used to create the new subscription
     * @param SubscriptionInterface[] $subscriptions Enabled subscriptions
     *
     * @return SubscriptionInterface
     *
     * @throws PermanentSubscriptionException
     */
    public function createSubscription(SubscriptionProductInterface $product, array $subscriptions = []);

    /**
     * @return ProductStrategyInterface
     */
    public function getProductStrategy();
}
