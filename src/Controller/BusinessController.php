<?php


namespace App\Controller;

use App\Annotation\HideSoftDeleted;
use App\Controller\Utils\UserTrait;
use App\Entity\Address;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusinessRepository;
use App\Enum\Store;
use Cocur\Slugify\SlugifyInterface;
use League\Geotools\Geotools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/{_locale}", requirements={ "_locale": "%locale_regex%" })
 * @HideSoftDeleted
 */
class BusinessController extends AbstractController
{
    use UserTrait;

    const ITEMS_PER_PAGE = 21;

    private $serializer;

    public function __construct(
        SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @Route("/businesses", name="businesses")
     */
    public function listAction(Request $request,
                               LocalBusinessRepository $repository,
                               CacheInterface $projectCache)
    {
        $mode = $request->query->get('mode', 'list');

        if (!in_array($mode, ['list', 'map'])) {
            $mode = 'list';
        }

        if ('map' === $mode) {

            return $this->render('business/list_map.html.twig', [
                'geohash' => $request->query->get('geohash'),
                'addresses_normalized' => $this->getUserAddresses(),
            ]);
        }

        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * self::ITEMS_PER_PAGE;

        if ($request->query->has('geohash') && strlen($request->query->get('geohash')) > 0) {
            $geotools = new Geotools();
            $geohash = $request->query->get('geohash');

            $decoded = $geotools->geohash()->decode($geohash);

            $latitude = $decoded->getCoordinate()->getLatitude();
            $longitude = $decoded->getCoordinate()->getLongitude();

            $matches = $repository->findByLatLng($latitude, $longitude);
        } else {

            $businessesIds = $projectCache->get('business.list.ids', function (ItemInterface $item) use ($repository) {

                $item->expiresAfter(60 * 5);

                return array_map(function (LocalBusiness $business) {

                    return $business->getId();
                }, $repository->findAllSorted());
            });

            $matches = array_map(function ($id) use ($repository) {
                return $repository->find($id);
            }, $businessesIds);

            $matches = array_values(array_filter($matches));
        }

        $count = count($matches);

        $matches = array_slice($matches, $offset, self::ITEMS_PER_PAGE);

        $pages = ceil($count / self::ITEMS_PER_PAGE);

        return $this->render('business/list.html.twig', array(
            'count' => $count,
            'businesses' => $matches,
            'page' => $page,
            'pages' => $pages,
            'geohash' => $request->query->get('geohash'),
            'addresses_normalized' => $this->getUserAddresses(),
            'address' => $request->query->has('address') ? $request->query->get('address') : null,
            'local_business_context' => $repository->getContext(),
        ));
    }

    /**
     * @Route("/businesses/map", name="businesses_map")
     */
    public function mapAction(Request $request, SlugifyInterface $slugify, CacheInterface $projectCache)
    {
        $businesses = $projectCache->get('homepage.map', function (ItemInterface $item) use ($slugify) {

            $item->expiresAfter(60 * 30);

            return array_map(function (LocalBusiness $business) use ($slugify) {

                return [
                    'name' => $business->getName(),
                    'address' => [
                        'geo' => [
                            'latitude'  => $business->getAddress()->getGeo()->getLatitude(),
                            'longitude' => $business->getAddress()->getGeo()->getLongitude(),
                        ]
                    ],
                    'url' => $this->generateUrl('business', [
                        'id' => $business->getId(),
                        'slug' => $slugify->slugify($business->getName())
                    ])
                ];
            }, $this->getDoctrine()->getRepository(LocalBusiness::class)->findBy(['enabled' => true]));
        });

        return $this->render('business/map.html.twig', [
            'businesses' => $this->serializer->serialize($businesses, 'json'),
        ]);
    }

    /**
     * @param string $type
     * @param int $id
     * @param string $slug
     * @param Request $request
     * @param SlugifyInterface $slugify
     * @param Address|null $address
     *
     * @Route("/{type}/{id}-{slug}", name="business",
     *   requirements={
     *     "type"="(business|store)",
     *     "id"="(\d+|__BUSINESS_ID__)",
     *     "slug"="([a-z0-9-]+)"
     *   },
     *   defaults={
     *     "slug"="",
     *     "type"="business"
     *   }
     * )
     */
    public function indexAction($type, $id, $slug, Request $request,
                                SlugifyInterface $slugify,
                                Address $address = null)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)->find($id);

        if (!$business) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('view', $business);

        $contextSlug = $this->getContextSlug($business);
        $expectedSlug = $slugify->slugify($business->getName());

        $redirectToCanonicalRoute = ($contextSlug !== $type) || ($slug !== $expectedSlug);

        if ($redirectToCanonicalRoute) {

            return $this->redirectToRoute('business', [
                'id' => $id,
                'slug' => $expectedSlug,
                'type' => $contextSlug,
            ], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->render('business/index.html.twig', array(
            'business' => $business,
            'addresses_normalized' => $this->getUserAddresses(),
        ));
    }

    private function getContextSlug(LocalBusiness $business)
    {
        return $business->getContext() === Store::class ? 'store' : 'business';
    }
}