<?php

namespace Astrada\SubscriptionBundle\Strategy;

use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use Astrada\SubscriptionBundle\Exception\ProductDefaultNotFoundException;

interface ProductStrategyInterface
{
    /**
     * Get final product.
     *
     * Determine the final based on your own algorithms.
     *
     * @param SubscriptionProductInterface $product
     *
     * @return SubscriptionProductInterface
     *
     * @throws ProductDefaultNotFoundException
     */
    public function getFinalProduct(SubscriptionProductInterface $product);
}
