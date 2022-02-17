<?php


namespace App\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TaskRepository extends EntityRepository
{
    public function findByDate(\DateTime $date)
    {
        $start = clone $date;
        $end = clone $date;

        return $this->findByDateRange($start, $end);
    }

    public function findByDateRange(\DateTime $start, \DateTime $end)
    {
        return self::addRangeClause($this->createQueryBuilder('t'), $start, $end)
            ->getQuery()
            ->getResult();
    }

    public static function addRangeClause(QueryBuilder $qb, \DateTime $start, \DateTime $end): QueryBuilder
    {
        // @see https://github.com/martin-georgiev/postgresql-for-doctrine
        // @see https://www.postgresql.org/docs/9.4/rangetypes.html
        // @see https://www.postgresql.org/docs/9.4/functions-range.html

        return $qb->andWhere('OVERLAPS(TSRANGE(t.doneAfter, t.doneBefore), CAST(:range AS tsrange)) = TRUE')
            ->setParameter('range', sprintf('[%s, %s]', $start->format('Y-m-d 00:00:00'), $end->format('Y-m-d 23:59:59')));
    }
}