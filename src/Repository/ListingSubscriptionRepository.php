<?php


namespace App\Repository;


use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use Astrada\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class ListingSubscriptionRepository extends EntityRepository implements SubscriptionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNumberOfSubscriptionsByProducts(SubscriptionProductInterface $product)
    {
        $qb = $this->createQueryBuilder('subscription')
            ->select('COUNT(subscription.id)')
            ->where('subscription.product = :product')
            ->setParameter('product', $product);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduct(SubscriptionProductInterface $product, $enabled = true)
    {
        $qb = $this->createQueryBuilder('subscription')
            ->where('subscription.product = :product')
            ->andWhere('subscription.active = :active')
            ->setParameter('product', $product)
            ->setParameter('active', $enabled);

        return $qb->getQuery()->getResult();
    }
}