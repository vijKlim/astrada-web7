<?php


namespace App\Controller;

use App\Annotation\HideSoftDeleted;
use App\Controller\Utils\UserTrait;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusinessRepository;
use App\Enum\HomeAndConstructionBusiness;
use App\Form\ListingSearchResultType;

use App\Repository\AddressRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Tetranz\Select2EntityBundle\Service\AutocompleteService;

class IndexController extends AbstractController
{
    use UserTrait;

    const MAX_RESULTS = 6;
    const EXPIRES_AFTER = 300;

    private $autocompleteService;

    public function __construct(AutocompleteService $autocompleteService)
    {
        $this->autocompleteService = $autocompleteService;
    }

    private function getItems(LocalBusinessRepository $repository, string $type, CacheInterface $cache, string $cacheKey)
    {
        $typeRerository = $repository->withContext($type);

        $itemsIds = $cache->get($cacheKey, function (ItemInterface $item) use ($typeRerository) {
            $item->expiresAfter(self::EXPIRES_AFTER);

            $items = $typeRerository->findAllSorted();

            return array_map(fn(LocalBusiness $lb) => $lb->getId(), $items);
        });

        foreach (array_slice($itemsIds, 0, self::MAX_RESULTS) as $id) {
            if(null == $typeRerository->find($id)) {
                $cache->delete($cacheKey);

                return $this->getItems($repository, $type, $cache, $cacheKey);
            }
        }

        $count = count($itemsIds);
        $items = array_map(
            fn(int $id): LocalBusiness => $typeRerository->find($id),
            array_slice($itemsIds, 0, self::MAX_RESULTS)
        );

        return [ $items, $count ];
    }

    private function getCityLocationsStats(AddressRepository $repository,  CacheInterface $cache, string $cacheKey)
    {

        $cityListingsStat = $cache->get($cacheKey, function (ItemInterface $item) use ($repository) {
            $item->expiresAfter(self::EXPIRES_AFTER);

            $items = $repository->countByType();

            return $items;
        });


        return array_reverse(array_slice($cityListingsStat, 0,5));
    }

    /**
     * @HideSoftDeleted
     */
    public function indexAction(LocalBusinessRepository $repository, AddressRepository $addressRepository,CacheInterface $projectCache)
    {
        $user = $this->getUser();

        if ($user && ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_BUSINESS'))) {
            $cacheKeySuffix = $user->getUsername();
        } else {
            $cacheKeySuffix = 'anonymous';
        }
        [$businesses, $businessesCount ] =
            $this->getItems($repository, HomeAndConstructionBusiness::class, $projectCache, sprintf('homepage.businesses.%s',$cacheKeySuffix));

        return $this->render('index/index.html.twig', array(
            'businesses' => $businesses,
            'city_locations_stats' => $this->getCityLocationsStats($addressRepository,$projectCache, sprintf('homepage.citiLocationsStats.%s',$cacheKeySuffix)),
            'show_more_businesses' => $businessesCount > self::MAX_RESULTS,
            'max_results' => self::MAX_RESULTS,
            'addresses_normalized' => $this->getUserAddresses(),
        ));
    }

    public function redirectToLocaleAction()
    {
        return new RedirectResponse(sprintf('/%s/', $this->getParameter('locale')), 302);
    }

    /**
     * @param Request $request
     *
     * @Route("/autocomplete", name="ajax_autocomplete")
     *
     * @return Response
     */
    public function autocompleteAction(Request $request)
    {
        // Check security etc. if needed

//        $as = $this->get('tetranz_select2entity.autocomplete_service');

        $result = $this->autocompleteService->getAutocompleteResults($request, ListingSearchResultType::class);

        return new JsonResponse($result);
    }
}