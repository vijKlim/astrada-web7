<?php


namespace App\Tests\App\Controller;


use App\Controller\BusinessController;
use App\Entity\Address;
use App\Entity\Base\GeoCoordinates;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusinessRepository;
use App\Entity\Sylius\Order;
use App\Sylius\Order\OrderItemInterface;
use App\Sylius\Cart\BusinessResolver;
use App\Sylius\Product\LazyProductVariantResolverInterface;
use App\Sylius\Product\ProductInterface;
use App\Sylius\Product\ProductVariantInterface;
use App\Utils\OptionsPayloadConverter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository as SyliusEntityRepository;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FindOneByCodeRepository extends SyliusEntityRepository
{
    public function findOneByCode($code)
    {
    }
}

class BusinessControllerTest extends WebTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        // FIXME
        // Find out why env is not test sometimes
        self::bootKernel(['environment' => 'test']);

        $this->objectManager = $this->prophesize(EntityManagerInterface::class);
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->productRepository = $this->prophesize(FindOneByCodeRepository::class);
        $this->orderItemRepository = $this->prophesize(RepositoryInterface::class);
        $this->orderItemFactory = $this->prophesize(FactoryInterface::class);
        $this->orderItemQuantityModifier = $this->prophesize(OrderItemQuantityModifierInterface::class);
        $this->orderModifier = $this->prophesize(OrderModifierInterface::class);
        $this->productVariantResolver = $this->prophesize(LazyProductVariantResolverInterface::class);
        $this->optionsPayloadConverter = $this->prophesize(OptionsPayloadConverter::class);
        $this->businessResolver = $this->prophesize(BusinessResolver::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        // Use the "real" serializer
        $this->serializer = self::$container->get('serializer');

        $this->localBusinessRepository = $this->prophesize(LocalBusinessRepository::class);

        $this->doctrine = $this->prophesize(ManagerRegistry::class);
        $this->doctrine
            ->getRepository(LocalBusiness::class)
            ->willReturn($this->localBusinessRepository->reveal());

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->has('doctrine')
            ->willReturn(true);
        $container
            ->get('doctrine')
            ->willReturn($this->doctrine->reveal());

        $parameterBag = $this->prophesize(ParameterBagInterface::class);
        $parameterBag->get('country_iso')->willReturn('ua');
        $parameterBag->get('sylius_cart_restaurant_session_key_name')->willReturn('foo');

        $container
            ->has('parameter_bag')
            ->willReturn(true);
        $container
            ->get('parameter_bag')
            ->willReturn($parameterBag->reveal());

        $this->controller = new BusinessController(
            $this->objectManager->reveal(),
            $this->validator->reveal(),
            $this->productRepository->reveal(),
            $this->orderItemRepository->reveal(),
            $this->orderItemFactory->reveal(),
            $this->productVariantResolver->reveal(),
            $this->orderItemQuantityModifier->reveal(),
            $this->orderModifier->reveal(),
            $this->serializer
        );

        $this->controller->setContainer($container->reveal());
    }

    private function setId($object, $id)
    {
        $property = new \ReflectionProperty($object, 'id');
        $property->setAccessible(true);
        $property->setValue($object, $id);
    }

    public function testAddProductToCartAction(): void
    {
        $productCode = Uuid::uuid4()->toString();
        $productOptionValueCode = Uuid::uuid4()->toString();

        $session = new Session(new MockArraySessionStorage());

        $request = Request::create('/business/{id}/cart/product/{code}', 'POST', [
            'options' => [
                [
                    'code' => $productOptionValueCode,
                    'quantity' => 3
                ]
            ]
        ]);
        $request->setSession($session);

        $businessAddress = new Address();
        $businessAddress->setGeo(new GeoCoordinates(48.856613, 2.352222));
        $this->setId($businessAddress, 1);

        $business = new LocalBusiness();
        $business->setAddress($businessAddress);
        $this->setId($business, 1);

        // Don't use a mock for the cart
        // because annotation reader won't work (for serialization)
        // https://github.com/doctrine/annotations/issues/186
        $cart = new Order();
        $cart->setBusiness($business);

        $product = $this->prophesize(ProductInterface::class);
        $product->isEnabled()->willReturn(true);
        $product->hasOptions()->willReturn(true);

        $business->getProducts()->add($product->reveal());

        $this->localBusinessRepository->find(1)->willReturn($business);

        $cartContext = $this->prophesize(CartContextInterface::class);
        $translator = $this->prophesize(TranslatorInterface::class);

        $cartContext
            ->getCart()
            ->willReturn($cart);

        $this->optionsPayloadConverter->convert($product->reveal(), [
                [
                    'code' => $productOptionValueCode,
                    'quantity' => 3,
                ]
            ])
            ->willReturn(new \SplObjectStorage());

        $this->productRepository
            ->findOneByCode($productCode)
            ->willReturn($product->reveal());

        $orderItem = $this->prophesize(OrderItemInterface::class);

        $this->orderItemFactory
            ->createNew()
            ->willReturn($orderItem->reveal());
        $variant = $this->prophesize(ProductVariantInterface::class);
        $variant->getPrice()->willReturn(900);

        $this->productVariantResolver
            ->getVariantForOptionValues($product->reveal(), Argument::type(\SplObjectStorage::class))
            ->willReturn($variant->reveal());

        $errors = $this->prophesize(ConstraintViolationListInterface::class);

        $this->validator
            ->validate(Argument::type('object'), Argument::any())
            ->will(function ($args) use ($cart, $errors) {

                if($args[0] === $cart) {

                    return $errors->reveal();
                }

                return $errors->reveal();
            });

        $response = $this->controller->addProductToCartAction(1, $productCode, $request,
            $cartContext->reveal(),
            $translator->reveal(),
            $this->businessResolver->reveal(),
            $this->optionsPayloadConverter->reveal(),
            $this->eventDispatcher->reveal()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode((string) $response->getContent(), true);

        $this->assertArrayHasKey('cart', $data);
        $this->assertArrayHasKey('times', $data);
        $this->assertArrayHasKey('errors', $data);
    }
}