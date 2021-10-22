<?php


namespace Astrada\SubscriptionBundle\Repository;

use Symfony\Component\Security\Core\User\UserInterface;
use Astrada\SubscriptionBundle\Model\SubscriptionInterface;
use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;

interface SubscriptionRepositoryInterface
{
    /**
     * Get number of subscriptions with associated product without regard to the state.
     *
     * @param SubscriptionProductInterface $product
     *
     * @return integer
     */
    public function getNumberOfSubscriptionsByProducts(SubscriptionProductInterface $product);

    /**
     * Find subscriptions by product and state.
     *
     * @param SubscriptionProductInterface $product
     * @param UserInterface    $user
     * @param boolean          $active
     *
     * @return SubscriptionInterface[]
     */
    public function findByProduct(SubscriptionProductInterface $product, $active = true);


}