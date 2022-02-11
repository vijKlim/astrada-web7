<?php


namespace App\Controller;

use App\Annotation\HideSoftDeleted;
use App\Controller\Utils\UserTrait;
use App\Entity\Address;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusinessRepository;
use App\Enum\Store;
use App\Form\Checkout\Action\AddProductToCartAction as CheckoutAddProductToCart;
use App\Form\Checkout\Action\Validator\AddProductToCart as AssertAddProductToCart;
use App\Form\Order\CartType;
use App\Sylius\Cart\BusinessResolver;
use App\Sylius\Order\OrderInterface;
use App\Utils\OptionsPayloadConverter;
use App\Utils\ValidationUtils;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Geotools\Geotools;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/{_locale}", requirements={ "_locale": "%locale_regex%" })
 * @HideSoftDeleted
 */
class BusinessController extends AbstractController
{
    use UserTrait;

    const ITEMS_PER_PAGE = 21;

    private $orderManager;



    private $serializer;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var RepositoryInterface
     */
    private RepositoryInterface $productRepository;
    private $productVariantResolver;
    private $orderItemFactory;
    private $orderItemQuantityModifier;
    private $orderModifier;

    public function __construct(
        EntityManagerInterface $orderManager,
        ValidatorInterface $validator,
        RepositoryInterface $productRepository,
        RepositoryInterface $orderItemRepository,
        $orderItemFactory,
        $productVariantResolver,
        $orderItemQuantityModifier,
        $orderModifier,
        SerializerInterface $serializer)
    {
        $this->orderManager = $orderManager;
        $this->validator = $validator;
        $this->productRepository = $productRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderItemFactory = $orderItemFactory;
        $this->orderItemQuantityModifier = $orderItemQuantityModifier;
        $this->orderModifier = $orderModifier;
        $this->productVariantResolver = $productVariantResolver;
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

    /**
     * @Route("/business/{id}/cart", name="business_cart", methods={"POST"})
     */
    public function cartAction($id, Request $request,
                               CartContextInterface $cartContext,
                               BusinessResolver $businessResolver)
    {
        $restaurant = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)->find($id);

        if (!$restaurant) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted('view', $restaurant);

        $cart = $cartContext->getCart();

        // This is useful to "cleanup" a cart that was stored
        // with a time range that is now expired
        // FIXME Maybe this should be moved to a Doctrine postLoad listener?
        $violations = $this->validator->validate($cart, null, ['ShippingTime']);
        if (count($violations) > 0) {

            $cart->setShippingTimeRange(null);

            if ($businessResolver->accept($cart)) {
                $this->orderManager->persist($cart);
                $this->orderManager->flush();
            }
        }

        $cartForm = $this->createForm(CartType::class, $cart);

        $cartForm->handleRequest($request);

        $cart = $cartForm->getData();

        $errors = [];

        if (!$cartForm->isValid()) {
            foreach ($cartForm->getErrors() as $formError) {
                $propertyPath = (string) $formError->getOrigin()->getPropertyPath();
                $errors[$propertyPath] = [ ValidationUtils::serializeFormError($formError) ];
            }
        }

        // Customer may be browsing the available businesses
        // Make sure the request targets the same business
        // If not, we don't persist the cart
        if ($businessResolver->accept($cart)) {
            $this->orderManager->persist($cart);
            $this->orderManager->flush();
        }

        return $this->jsonResponse($cart, $errors);
    }

    /**
     * @Route("/business/{id}/cart/product/{code}", name="business_add_product_to_cart", methods={"POST"})
     */
    public function addProductToCartAction($id, $code, Request $request,
                                           CartContextInterface $cartContext,
                                           TranslatorInterface $translator,
                                           BusinessResolver $businessResolver,
                                           OptionsPayloadConverter $optionsPayloadConverter)
    {
        $business = $this->getDoctrine()
            ->getRepository(LocalBusiness::class)->find($id);

        $product = $this->productRepository->findOneByCode($code);

        $cart = $cartContext->getCart();

        $action = new CheckoutAddProductToCart();
        $action->product = $product;
        $action->cart = $cart;
        $action->clear = $request->request->getBoolean('_clear', false);

        $violations = $this->validator->validate($action, new AssertAddProductToCart());

        if (count($violations) > 0) {

            $errors = [];
            foreach ($violations as $violation) {
                $key = $violation->getPropertyPath();
                $errors[$key][] = [
                    'message' => $violation->getMessage()
                ];
            }

            return $this->jsonResponse($cart, $errors);
        }

        $cartItem = $this->orderItemFactory->createNew();

        if (!$product->hasOptions()) {
            $productVariant = $this->productVariantResolver->getVariant($product);
        } else {
            if (!$request->request->has('options') && !$product->hasNonAdditionalOptions()) {
                $productVariant = $this->productVariantResolver->getVariant($product);
            } else {
                $optionValues = $optionsPayloadConverter->convert($product, $request->request->get('options'));
                $productVariant = $this->productVariantResolver->getVariantForOptionValues($product, $optionValues);
            }
        }

        $cartItem->setVariant($productVariant);
        $cartItem->setUnitPrice($productVariant->getPrice());

        $this->orderItemQuantityModifier->modify($cartItem, $request->request->getInt('quantity', 1));
        $this->orderModifier->addToOrder($cart, $cartItem);

        $this->orderManager->persist($cart);
        $this->orderManager->flush();

        $errors = $this->validator->validate($cart);
        $errors = ValidationUtils::serializeViolationList($errors);

        return $this->jsonResponse($cart, $errors);
    }


    private function getContextSlug(LocalBusiness $business)
    {
        return $business->getContext() === Store::class ? 'store' : 'business';
    }

    private function jsonResponse(OrderInterface $cart, array $errors)
    {
        $country = $this->getParameter('country_iso');

        $serializerContext = [
            'is_web' => true,
            'groups' => ['order', 'address', sprintf('address_%s', $country)]
        ];

        return new JsonResponse([
            'cart'   => $this->serializer->normalize($cart, 'jsonld', $serializerContext),
            'errors' => $errors,
        ]);
    }
}