<?php


namespace App\Api\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\CollectionDataProvider;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use App\Entity\LocalBusiness;
use App\Utils\BusinessFilter;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

final class BusinessCollectionDataProvider
    extends CollectionDataProvider
{
    private $businessFilter;

    public function __construct(
        ManagerRegistry $managerRegistry,
        iterable $collectionExtensions = [],
        BusinessFilter $businessFilter)
    {
        parent::__construct($managerRegistry, $collectionExtensions);

        $this->businessFilter = $businessFilter;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $supports = false;
        if (LocalBusiness::class === $resourceClass && $operationName === 'get') {
            $supports = isset($context['filters']) && isset($context['filters']['coordinate']);
        }

        return $supports;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = [])
    {
        $collection = parent::getCollection($resourceClass, $operationName, $context);

        [ $latitude, $longitude ] = explode(',', $context['filters']['coordinate']);

        return $this->businessFilter->matchingLatLng($collection, $latitude, $longitude);
    }
}
