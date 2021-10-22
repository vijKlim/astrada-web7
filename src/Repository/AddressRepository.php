<?php

namespace App\Repository;



use Doctrine\ORM\EntityRepository;

class AddressRepository extends EntityRepository
{
    public function countByType(): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->select('r.addressLocality as city')
            ->addSelect('COUNT(r.id) AS cnt')
            ->where('r.addressLocality != \'\'')
            ->groupBy('r.addressLocality')
            ->orderBy('cnt', 'DESC')
        ;

        return $qb->getQuery()->getArrayResult();
    }
}