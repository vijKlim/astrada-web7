<?php


namespace App\Controller;

use App\Controller\Utils\UserTrait;
use App\Entity\Listing;
use App\Entity\ListingRepository;
use App\Entity\Model\ListingSearchRequest;
use App\Form\ListingSearchHomeType;
use App\Form\ListingSearchResultType;
use App\Service\Geocoder;
use App\Service\UserSearchAddresses;
use App\Utils\GeoUtils;

use Doctrine\ORM\Tools\Pagination\Paginator;
use League\Geotools\Geotools;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;

use Pitch\Liform\LiformInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ListingSearchController extends AbstractController
{
    use UserTrait;

    private $translator;
    private $listingSearchRequest;
    private $currencyContext;
    /**
     * @var UploaderHelper
     */
    private UploaderHelper $uploaderHelper;

    protected LiformInterface $liform;


    public function __construct(TranslatorInterface $translator,
    ListingSearchRequest $listingSearchRequest,
    CurrencyContextInterface $currencyContext,
    UploaderHelper $uploaderHelper,
    LiformInterface $liform)
    {
        $this->translator = $translator;
        $this->listingSearchRequest = $listingSearchRequest;
        $this->currencyContext = $currencyContext;
        $this->uploaderHelper = $uploaderHelper;
        $this->liform = $liform;
    }

    /**
     * Listings search result.
     *
     * @Route("/listing/nearby", name="listing_nearby")
     * @Method("GET")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function nearbyAction(Request $request, ListingRepository $repository)
    {

        $listings = new \ArrayIterator();
        $nbListings = 0;

        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->listingSearchRequest;

        $geotools = new Geotools();
        $geohash = $listingSearchRequest->getGeohash();

        $decoded = $geotools->geohash()->decode($geohash);

        $latitude = $decoded->getCoordinate()->getLatitude();
        $longitude = $decoded->getCoordinate()->getLongitude();
        $offset = ($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage();
        //30000 - 30 км
        $results = $repository->findNearby($latitude, $longitude,30000,$listingSearchRequest->getMaxPerPage(), $offset);
        $nbListings = $results->count();
        $listings = $results->getIterator();

        $this->get('session')->set('listing_search_request', $listingSearchRequest);

        $allListingsNormalized = array_map(function (Listing $listing) {
            return $this->get('serializer')->normalize($listing, 'jsonld', [
                'resource_class' => Listing::class,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
                'groups' => ['listing_public']
            ]);
        }, $listings->getArrayCopy());

        return new JsonResponse(['listings' => $allListingsNormalized]);
    }



    /**
     * Listings search result.
     *
     * @Route("/listing/search_result", name="listing_search_result")
     * @Method("GET")
     *
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request, ListingRepository $repository,
                                 Geocoder $geocoder, CacheInterface $projectCache)
    {
        //For drag map mode
        $isXmlHttpRequest = $request->isXmlHttpRequest() ? true : false;

        $markers = array('listingsIds' => array(), 'markers' => array());
        $listings = new \ArrayIterator();
        $nbListings = 0;

        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $this->listingSearchRequest;
        $isXmlHttpRequest ? $listingSearchRequest->setSortBy('distance') : null;
        $form = $this->createSearchResultForm($listingSearchRequest);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listingSearchRequest = $form->getData();

            $geotools = new Geotools();
            $geohash = $listingSearchRequest->getGeohash();

            $decoded = $geotools->geohash()->decode($geohash);

            $latitude = $decoded->getCoordinate()->getLatitude();
            $longitude = $decoded->getCoordinate()->getLongitude();
            $offset = ($listingSearchRequest->getPage() - 1) * $listingSearchRequest->getMaxPerPage();
            //50000 - 50 км
            $results = $repository->findNearby($latitude, $longitude,50000,$listingSearchRequest->getMaxPerPage(), $offset);
            $nbListings = $results->count();
            $listings = $results->getIterator();

            $listingsNormalized = array_map(function (Listing $listing) {
                return $this->get('serializer')->normalize($listing, 'jsonld', [
                    'resource_class' => Listing::class,
                    'operation_type' => 'item',
                    'item_operation_name' => 'get',
                    'groups' => ['listing_public']
                ]);
            }, $listings->getArrayCopy());

            //TODO make Address database
            $search_address = $projectCache->get($geohash, function (ItemInterface $item) use ($geocoder, $latitude, $longitude) {

                $item->expiresAfter(60 * 5);

                return $geocoder->reverse($latitude, $longitude);
            });




//            foreach ($listings as &$listing){
//                $geo = GeoUtils::asGeoCoordinates($listing['address']['geo']);
//                $listing['location']['coordinate']['lat'] = $geo->getLatitude();
//                $listing['location']['coordinate']['lng'] = $geo->getLongitude();
//            }

//            $results = $this->get("cocorico.listing_search.manager")->search(
//                $listingSearchRequest,
//                $request->getLocale()
//            );
//            $nbListings = $results->count();
//            $listings = $results->getIterator();

//
//            //Persist similar listings id
//            $listingSearchRequest->setSimilarListings($markers['listingsIds']);
//
            //Persist listing search request in session
            !$isXmlHttpRequest ? $this->get('session')->set('listing_search_request', $listingSearchRequest) : null;
        } else {
            foreach ($form->getErrors(true) as $error) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    /** @Ignore */
                    $this->translator->trans($error->getMessage(), $error->getMessageParameters())
                );
            }
        }



        //Breadcrumbs
//        $breadcrumbs = $this->get('cocorico.breadcrumbs_manager');
//        $breadcrumbs->addListingResultItems($this->get('request_stack')->getCurrentRequest(), $listingSearchRequest);

        return $this->render(
            $isXmlHttpRequest ?
                'listingResult/result_ajax.html.twig' :
//                'listingResult/result.html.twig',
                'listingResult/result.html.twig',

            array(
                'date' => (new \DateTime())->format('Y-m-d'),
                'form' => $form->createView(),
                'search_address' => $search_address,
                'listingsNormalized' => $listingsNormalized,
                'listings' => $listings,
                'nb_listings' => $nbListings,

                'listing_search_request' => $listingSearchRequest,
                'json_searchform_schema' => json_encode($this->liform->transform($form->createView())),
                'pagination' => array(
                    'page' => $listingSearchRequest->getPage(),
                    'pages_count' => ceil($nbListings / $listingSearchRequest->getMaxPerPage()),
                    'route' => $request->get('_route'),
                    'route_params' => $request->query->all()
                ),
                'addresses_normalized' => $this->getUserAddresses(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    protected function createSearchResultForm(ListingSearchRequest $listingSearchRequest)
    {
//        $form = $this->get('form.factory')->createNamed(
//            '',
//            ListingSearchResultType::class,
//            $listingSearchRequest,
//            array(
//                'method' => 'GET',
//                'action' => $this->generateUrl('listing_search_result'),
//            )
//        );

        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchHomeType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('listing_search_result'),
            )
        );

        return $form;
    }



    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchHomeFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchHomeForm($listingSearchRequest);

        return $this->render(
            'common/form/form_search.html.twig',
            array(
                'form' => $form->createView(),
                'addresses_normalized' => $this->getUserAddresses(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createSearchHomeForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchHomeType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('listing_search_result'),
            )
        );

        return $form;
    }

    /**
     *
     * @return Response
     */
    public function searchHeaderFormAction()
    {
        $listingSearchRequest = $this->getListingSearchRequest();
        $form = $this->createSearchHeaderForm($listingSearchRequest);

        return $this->render(
            'form/header_search.html.twig',
            array(
                'form' => $form->createView(),
                'addresses_normalized' => $this->getUserAddresses(),
            )
        );
    }

    /**
     * @param  ListingSearchRequest $listingSearchRequest
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createSearchHeaderForm(ListingSearchRequest $listingSearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            ListingSearchHomeType::class,
            $listingSearchRequest,
            array(
                'method' => 'GET',
                'action' => $this->generateUrl('listing_search_result'),
            )
        );

        return $form;
    }

    /**
     * @return ListingSearchRequest
     */
    protected function getListingSearchRequest()
    {
        $session = $this->get('session');
        /** @var ListingSearchRequest $listingSearchRequest */
        $listingSearchRequest = $session->has('listing_search_request') ?
            $session->get('listing_search_request') :
            $this->listingSearchRequest;

        return $listingSearchRequest;
    }
}