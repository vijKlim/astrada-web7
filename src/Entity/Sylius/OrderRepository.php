<?php


namespace App\Entity\Sylius;

use App\Entity\LocalBusiness;
use App\Sylius\Order\OrderInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Sylius\Bundle\OrderBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;
use Sylius\Component\Customer\Model\CustomerInterface;

use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OrderRepository extends BaseOrderRepository
{
    public function findCartsByBusiness(LocalBusiness $business)
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.state = :state')
            ->setParameter('state', OrderInterface::STATE_CART)
        ;

        $qb = self::addBusinessClause($qb, 'o', $business);

        return $qb->getQuery()->getResult();
    }

    public  function addBusinessClause(QueryBuilder $qb, $alias, LocalBusiness $business, $vendorAlias = 'v')
    {
        return $qb
            ->join(OrderVendor::class, $vendorAlias, Join::WITH, sprintf('%s.id = %s.order', $alias, $vendorAlias))
            ->andWhere(sprintf('%s.business = :business', $vendorAlias))
            ->setParameter('business', $business);
    }
}