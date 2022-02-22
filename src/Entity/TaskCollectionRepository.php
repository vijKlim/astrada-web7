<?php


namespace App\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class TaskCollectionRepository extends EntityRepository
{
    public function findByTask(Task $task)
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->join(TaskCollectionItem::class, 'i', Expr\Join::WITH, 'i.parent = c.id')
            ->andWhere('i.task = :task')
            ->setParameter('task', $task)
            ;

        return $qb->getQuery()->getResult();
    }
}