<?php


namespace App\Repository;

use App\Entity\ListingAvailability;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class ListingAvailabilityRepository extends EntityRepository
{
    /**
     *
     * Get all ListingAvailability documents by date range and listing
     *
     * @param int       $listingId
     * @param \DateTime $start
     * @param \DateTime $end
     * @param boolean   $endDayIncluded
     * @param boolean   $hydrate
     *
     * @return ListingAvailability[]
     */
    public function findAvailabilitiesByListing(
        $listingId,
        \DateTime $start,
        \DateTime $end,
        $endDayIncluded = false
    ) {


        return self::addRangeClause($this->createQueryBuilder('t'), $start, $end)
            ->getQuery()
            ->getResult();
    }

    public static function addRangeClause(QueryBuilder $qb, \DateTime $start, \DateTime $end): QueryBuilder
    {
        // @see https://github.com/martin-georgiev/postgresql-for-doctrine
        // @see https://www.postgresql.org/docs/9.4/rangetypes.html
        // @see https://www.postgresql.org/docs/9.4/functions-range.html
        return $qb
            //->andWhere('t.day BETWEEN :start AND :end')
            ->andWhere('t.day >= :start')
            ->andWhere('t.day <= :end')

            ->setParameter('start',  $start->format('Y-m-d'))
            ->setParameter('end',  $end->format('Y-m-d'));
        }
}