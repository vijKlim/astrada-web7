<?php


namespace App\Entity;


use App\Entity\LocalBusiness\FindNearbyInterface;
use App\Entity\LocalBusiness\FindNearbyTrait;
use Astrada\SubscriptionBundle\Repository\ProductRepositoryInterface;
use Doctrine\ORM\EntityRepository;
//use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ListingRepository extends EntityRepository implements FindNearbyInterface, ProductRepositoryInterface
{
    use FindNearbyTrait;

    /**
     * {@inheritdoc}
     */
    public function findDefault()
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.default = :default')
            ->setParameter('default', true);

        return $qb->getQuery()->getOneOrNullResult();
    }
}