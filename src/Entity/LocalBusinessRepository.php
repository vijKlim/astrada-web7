<?php

namespace App\Entity;

use App\Entity\LocalBusiness\FindNearbyInterface;
use App\Entity\LocalBusiness\FindNearbyTrait;
use App\Enum\HomeAndConstructionBusiness;

use App\Utils\BusinessFilter;
use Carbon\Carbon;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

class LocalBusinessRepository extends EntityRepository implements FindNearbyInterface
{
    use FindNearbyTrait;

    private $businessFilter;
    private $context = HomeAndConstructionBusiness::class;

    public function withContext(string $context)
    {
        $repository = clone $this;

        return $repository->setContext($context);
    }

    public function setBusinessFilter(BusinessFilter $businessFilter)
    {
        $this->businessFilter = $businessFilter;

        return $this;
    }

    public function setContext(string $context)
    {
        $this->context = $context;

        return $this;
    }

    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * We will obsviously have a fairly small amount of businesses.
     * So, there is not significant performance downside in loading them all.
     * Event with 50 businesses, it takes ~ 500ms to complete.
     */
    public function findByLatLng($latitude, $longitude)
    {
        return $this->businessFilter->matchingLatLng($this->findAll(), $latitude, $longitude);
    }

    public function search($q)
    {
        $qb = $this->createQueryBuilder('r');

        $qb
            ->where('LOWER(r.name) LIKE :q')
            ->setParameter('q', '%' . strtolower($q) . '%');

        return $qb->getQuery()->getResult();
    }

    public function findRandom($maxResults = 3)
    {
        // Do not use ORDER BY RAND()
        // @see https://github.com/doctrine/doctrine2/issues/5479
        $qb = $this->createQueryBuilder('r');

        $rows = $qb
            ->select('r.id')
            ->getQuery()
            ->getArrayResult();

        shuffle($rows);

        $rows = array_slice($rows, 0, $maxResults);

        $ids = array_map(function ($row) {
            return $row['id'];
        }, $rows);

        return $this->createQueryBuilder('r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', array_values($ids))
            ->getQuery()
            ->getResult();
    }

    public function countAll()
    {
        $qb = $this
            ->createQueryBuilder('r')
            ->select('COUNT(r)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllSorted()
    {
        $qb = $this->createQueryBuilder('o');

        $r = new \ReflectionClass($this->context);
        $values = $r->getMethod('values')->invoke(null);

        $types = [];
        foreach ($values as $value) {
            $types[] = $value->getValue();
        }

        $qb->add('where', $qb->expr()->in('o.type', $types));

        $matches = $qb->getQuery()->getResult();

        // 0 - featured & opened restaurants
        // 1 - opened businesses
        // 2 - closed businesses
        // 3 - disabled businesses

        $now = Carbon::now();

        $nextOpeningComparator = function (LocalBusiness $a, LocalBusiness $b) use ($now) {

            $aNextOpening = $a->getNextOpeningDate($now);
            $bNextOpening = $b->getNextOpeningDate($now);

            $compareNextOpening = $aNextOpening === $bNextOpening ?
                0 : ($aNextOpening < $bNextOpening ? -1 : 1);

            return $compareNextOpening;
        };

        usort($matches, $nextOpeningComparator);

        $featured = array_filter($matches, function (LocalBusiness $lb) use ($now) {
            return $lb->isFeatured() && $lb->isOpen($now);
        });
        $opened = array_filter($matches, function (LocalBusiness $lb) use ($now, $featured) {
            return !in_array($lb, $featured, true) && $lb->isOpen($now);
        });
        $closed = array_filter($matches, function (LocalBusiness $lb) use ($now) {
            return !$lb->isOpen($now);
        });

        return array_merge($featured, $opened, $closed);
    }

    public function findByOption(ProductOptionInterface $option)
    {
        // @see https://stackoverflow.com/questions/33346113/doctrine2-manytomany-inverse-querybuilder
        $qb = $this->createQueryBuilder('r')
            ->innerJoin('r.productOptions', 'o')
            ->andWhere('o.id = :option')
            ->setParameter('option', $option)
        ;

        return $qb->getQuery()->getResult();
    }

    private function createZeroWasteQueryBuilder()
    {
        $qb = $this->createQueryBuilder('r');
        $qb
            ->andWhere(
                'r.enabled = :enabled'
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('r.depositRefundEnabled', ':enabled'),
                    $qb->expr()->eq('r.loopeatEnabled', ':enabled')
                )
            )
            ->setParameter('enabled', true);

        return $qb;
    }

    public function findZeroWaste()
    {
        return $this->createZeroWasteQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    public function countZeroWaste()
    {
        $qb = $this->createZeroWasteQueryBuilder();
        $qb
            ->select('COUNT(r.id)');

        return $qb->getQuery()
            ->getSingleScalarResult();
    }
}
