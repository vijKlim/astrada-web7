<?php


namespace App\Tests\App\Controller;


use App\Controller\BusinessController;
use App\Entity\Address;
use App\Entity\Base\GeoCoordinates;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusinessRepository;
use App\Entity\Sylius\Order;
use App\Sylius\Product\ProductInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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
        // Use the "real" serializer
        $this->serializer = static::$kernel->getContainer()->get('serializer');

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
                'code' => $productOptionValueCode,
                'quantity' => 3
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
    }
}