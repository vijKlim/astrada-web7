<?php


namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;


class ListingReviewRepository extends EntityRepository
{
    /**
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function createQueryBuilderByListingId($listingId)
    {
        $queryBuilder = $this->createQueryBuilder('o');
        $queryBuilder
            ->join('o.reviewSubject', 'listing')
            ->andWhere('listing = :listing')
            ->setParameter('listing', $listingId);

        return $queryBuilder;
    }
}
