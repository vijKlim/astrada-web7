<?php

namespace Astrada\SubscriptionBundle\Repository;



use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;

interface ProductRepositoryInterface
{
    /**
     * Find a default product.
     *
     * @return SubscriptionProductInterface|null
     */
    public function findDefault();
}