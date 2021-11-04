<?php


namespace App\Controller;

use App\Annotation\HideSoftDeleted;
use ApiPlatform\Core\Api\IriConverterInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\Controller\Utils\AccessControlTrait;
use App\Controller\Utils\BusinessListingTrait;
use App\Controller\Utils\BusinessTrait;
use App\Entity\Address;
use App\Entity\BusinessListingList;
use App\Entity\Listing;
use App\Entity\ListingRepository;
use App\Entity\LocalBusiness;
use App\Form\ListingPricingRuleSetType;
use App\Serializer\BusinessListingListNormalizer;
use App\Serializer\PrivateListingNormalizer;
use App\Service\SettingsManager;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\Hashids;
use Knp\Component\Pager\PaginatorInterface;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geotools;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManagerInterface;
use phpcent\Client as CentrifugoClient;
use Redis;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends AbstractController
{
    use AccessControlTrait;
    use BusinessTrait;
    use BusinessListingTrait;

    private $settingsManager;
    private $businessListingPositionsNormalizer;

    public function __construct(
        TranslatorInterface $translator,
        SettingsManager $settingsManager,
        BusinessListingListNormalizer $businessListingPositionsNormalizer) {

        $this->translator = $translator;
        $this->settingsManager = $settingsManager;
        $this->businessListingPositionsNormalizer = $businessListingPositionsNormalizer;
    }

    protected function getBusinessRoutes()
    {
        return [
            'businesses' => 'dashboard_businesses',
            'business' => 'dashboard_business',
            'service_taxon' => 'dashboard_business_service_taxon',
            'service_taxons' => 'dashboard_business_service_taxons',
            'products' => 'dashboard_business_products',
            'product_options' => 'dashboard_business_product_options',
            'product_new' => 'dashboard_business_product_new',
            'listings' => 'dashboard_business_listings',
            'listing_new' => 'dashboard_business_listing_new',
            'dashboard' => 'dashboard_business_dashboard',
            'planning' => 'dashboard_business_planning',
            'stats' => 'dashboard_business_stats',
            'deposit_refund' => 'dashboard_business_deposit_refund',
            'promotions' => 'dashboard_business_promotions',
            'promotion_new' => 'dashboard_business_new_promotion',
            'promotion' => 'dashboard_business_promotion',
            'product_option_preview' => 'dashboard_business_product_option_preview',
        ];
    }

    protected function getListingRoutes()
    {
        return [
            'listings' => 'dashboard_listings',
            'listing' => 'dashboard_listing',
        ];
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboardAction(Request $request,
                                JWTManagerInterface $jwtManager,
                                CentrifugoClient $centrifugoClient,
                                Redis $tile38,
                                IriConverterInterface $iriConverter,
                                    ListingRepository $listingRepository)
    {
        $mapCenterValue = explode(',', $this->settingsManager->get('latlng'));

        $geotools = new Geotools();
        $geohash = $geotools->geohash()->encode(new Coordinate($mapCenterValue))->getGeohash();

        return $this->dashboardFullscreenAction($geohash,$request, $jwtManager,
            $centrifugoClient, $tile38, $iriConverter, $listingRepository);
    }

    /**
     * @Route("/dashboard/fullscreen/{geohash}", name="dashboard_fullscreen",
     *   requirements={"geohash"=".+"})
     */
    public function dashboardFullscreenAction($geohash, Request $request,
                                              JWTManagerInterface $jwtManager,
                                              CentrifugoClient $centrifugoClient,
                                              Redis $tile38,
                                              IriConverterInterface $iriConverter,
                                              ListingRepository $listingRepository)
    {

        $user = $this->getUser();

        if (!$user->hasRole('ROLE_BUSINESS') || !$request->attributes->has('_business')) {
            return $this->redirectToRoute('nucleos_profile_profile_show');

        }

        $_business = $request->attributes->get('_business');

        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($_business->getId());

        $this->accessControl($business);

        $hashids = new Hashids($this->getParameter('secret'), 8);

        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }

        $geotools = new Geotools();
        $decoded = $geotools->geohash()->decode($geohash);

        $latitude = $decoded->getCoordinate()->getLatitude();
        $longitude = $decoded->getCoordinate()->getLongitude();
        //5000 - 5 км
        $results = $listingRepository->findNearby($latitude, $longitude,5000, 1000, 0);
        $allListings = $results->getIterator();

        $allListingsNormalized = array_map(function (Listing $listing) {
            return $this->get('serializer')->normalize($listing, 'jsonld', [
                'resource_class' => Listing::class,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
                'groups' => ['listing_public']
            ]);
        }, $allListings->getArrayCopy());



        $listingList = [];

        /** @var Listing $listing */
        foreach ($allListings as $listing){
            if(!isset($listingList[$listing->getBusiness()->getId()])){
                $listingList[$listing->getBusiness()->getId()] = new BusinessListingList($listing->getBusiness());
            }
            $listingList[$listing->getBusiness()->getId()]->addItem($listing);
        }


//        $listingListNormalized = array_map(function (BusinessListingList $businessListingPos) {
//            return $this->businessListingPositionsNormalizer->normalize($businessListingPos);
//        }, $listingList);

        $listingListNormalized = array_map(function (BusinessListingList $businessListingList) {
            return $this->get('serializer')->normalize($businessListingList, 'jsonld', [
                'resource_class' => BusinessListingList::class,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
                'groups' => ['listing_public']
            ]);
        }, array_values($listingList));


        $myListingsNormalized = $business->getListings()->map(function($listing) {
            return $this->get('serializer')->normalize($listing, 'jsonld', [
                'resource_class' => Listing::class,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
                'groups' => ['listing']
            ]);
        });

        $this->getDoctrine()->getManager()->getFilters()->enable('soft_deleteable');

        $qb = $this->getDoctrine()
            ->getRepository(Address::class)
            ->createQueryBuilder('a');
        $qb
            ->select('a.id')
            ->leftJoin(LocalBusiness::class, 'r', Expr\Join::WITH, 'r.address = a.id')
            ->leftJoin(Listing::class,           'h', Expr\Join::WITH, 'h.address = a.id')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNotNull('r.id'),
                    $qb->expr()->isNotNull('h.id')
                )
            );

        $addressIris = array_map(
            fn ($address) => $iriConverter->getItemIriFromResourceClass(Address::class, $address),
            $qb->getQuery()->getArrayResult()
        );

        return $this->render('admin/dashboard_iframe.html.twig', [
            'nav' => $request->query->getBoolean('nav', true),
            'date' => (new \DateTime())->format('Y-m-d'),
            'my_listings' => $myListingsNormalized,
            'all_listings' => $allListingsNormalized,
            'listing_lists' => $listingListNormalized,
            'jwt' => $jwtManager->create($this->getUser()),
            'centrifugo_token' => $centrifugoClient->generateConnectionToken($this->getUser()->getUsername(), (time() + 3600)),
            'centrifugo_tracking_channel' => sprintf('$%s_tracking', $this->getParameter('centrifugo_namespace')),
            'centrifugo_events_channel' => sprintf('%s_events#%s', $this->getParameter('centrifugo_namespace'), $this->getUser()->getUsername()),
            'pickup_cluster_addresses' => $addressIris,
        ]);
    }


    /**
     * @Route("/dashboard/businesses/{id}/listings_pricing", name="dashboard_business_listings_pricing")
     */
    public function listingsPricingRuleSetsAction($id, Request $request)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $ruleSets = $this->getDoctrine()
            ->getRepository(Listing\ListingPricingRuleSet::class)
            ->findAll();


        return $this->render('business/listings_pricing.html.twig', $this->withRoutes([
            'layout' => 'dashboard.html.twig',
            'ruleSets' => $ruleSets,
            'business' => $business,
        ],[]));

    }

    private function renderPricingRuleSetForm(LocalBusiness $business, Listing\ListingPricingRuleSet $ruleSet, Request $request)
    {
        $originalRules = new ArrayCollection();

        foreach ($ruleSet->getRules() as $rule) {
            $originalRules->add($rule);
        }



        $form = $this->createForm(ListingPricingRuleSetType::class, $ruleSet);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ruleSet = $form->getData();

            $em = $this->getDoctrine()->getManagerForClass(Listing\ListingPricingRule::class);

            foreach ($originalRules as $originalRule) {
                if (!$ruleSet->getRules()->contains($originalRule)) {
                    $em->remove($originalRule);
                }
            }

            foreach ($ruleSet->getRules() as $rule) {
                $rule->setRuleSet($ruleSet);
            }

            if (null === $ruleSet->getId()) {
                $em->persist($ruleSet);
            }

            $em->flush();

            $this->addFlash(
                'notice',
                $this->translator->trans('global.changesSaved')
            );

            return $this->redirectToRoute('dashboard_deliveries_pricing_ruleset', ['businessId'=>$business->getId(), 'pricingId' => $ruleSet->getId()]);
        }

        return $this->render('business/listings_pricing_ruleset.html.twig', $this->withRoutes([
            'layout' => 'dashboard.html.twig',
            'form' => $form->createView(),
            'business' => $business
        ],[]));
    }

    /**
     * @Route("/dashboard/businesses/{id}/listings_pricing/new", name="dashboard_business_listings_pricing_ruleset_new")
     */
    public function newListingsPricingRuleSetAction($id,Request $request)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($id);

        $this->accessControl($business);

        $ruleSet = new Listing\ListingPricingRuleSet();

        return $this->renderPricingRuleSetForm($business, $ruleSet, $request);
    }

    /**
     * @Route("/dashboard/businesses/{businessId}/listings_pricing/{pricingId}", name="dashboard_business_listings_pricing_ruleset")
     */
    public function listingsPricingRuleSetAction($businessId, $pricingId, Request $request)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)
            ->find($businessId);

        $this->accessControl($business);

        $ruleSet = $this->getDoctrine()
            ->getRepository(Delivery\PricingRuleSet::class)
            ->find($pricingId);

        return $this->renderPricingRuleSetForm($business, $ruleSet, $request);
    }
}