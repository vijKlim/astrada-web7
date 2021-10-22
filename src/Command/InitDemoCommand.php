<?php


namespace App\Command;

use App\Entity;
use App\Faker\AddressProvider;
use App\Faker\BusinessProvider;
use App\Faker\ListingProvider;
use App\Service\Geocoder;
use Craue\ConfigBundle\Util\Config;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker;
use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Geocoder\Provider\Chain\Chain as ChainProvider;
use Geocoder\Provider\Photon\Photon as PhotonProvider;
use GuzzleHttp\HandlerStack;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client;
use League\Geotools\Coordinate\Coordinate;
use libphonenumber\PhoneNumberUtil;
use Nucleos\UserBundle\Util\UserManipulator;
use Redis;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

class InitDemoCommand extends Command
{
    private $doctrine;
    private $faker;
    private $fixturesLoader;
    private $redis;
    private $lockFactory;
    private $userManipulator;
    private $taxonFactory;
    private $phoneNumberUtil;
    private $batchSize = 10;
    private $excludedTables = [
        'craue_config_setting',
        'migration_versions',
        'sylius_locale',
        'sylius_channel',
        'sylius_tax_category',
        'sylius_tax_rate',
    ];

    private static $users = [
        'admin' => [
            'password' => '3dsp20m3d',
            'roles' => ['ROLE_ADMIN']
        ],
    ];

    private static $kievUkraineCoords = [
        50.450001,
        30.523333
    ];

    protected function configure()
    {
        $this
            ->setName('astrada:demo:init')
            ->setDescription('Initialize Astrada demo.');
    }

    public function __construct(
        ManagerRegistry $doctrine,
        UserManipulator $userManipulator,
        LoaderInterface $fixturesLoader,
        Faker\Generator $faker,
        Redis $redis,
        Config $craueConfig,
        string $configEntityName,
        FactoryInterface $taxonFactory,
        PhoneNumberUtil $phoneNumberUtil,
        Geocoder $geocoder,
        string $country,
        string $defaultLocale)
    {
        $this->doctrine = $doctrine;
        $this->fixturesLoader = $fixturesLoader;
        $this->faker = $faker;
        $this->redis = $redis;
        $this->craueConfig = $craueConfig;
        $this->configEntityName = $configEntityName;
        $this->userManipulator = $userManipulator;
        $this->taxonFactory = $taxonFactory;
        $this->phoneNumberUtil = $phoneNumberUtil;
        $this->geocoder = $geocoder;
        $this->country = $country;
        $this->defaultLocale = $defaultLocale;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $providerClass = 'App\\Faker\\' . $this->defaultLocale . '\\BusinessProvider';
        if (!class_exists($providerClass, true)) {
            $providerClass = BusinessProvider::class;
        }
        $providerClass2 = 'App\\Faker\\' . $this->defaultLocale . '\\ListingProvider';
        if (!class_exists($providerClass2, true)) {
            $providerClass2 = ListingProvider::class;
        }


        $businessProvider = new $providerClass($this->faker);
        $listingProvider = new $providerClass2($this->faker);

        $this->faker->addProvider($businessProvider);
        $this->faker->addProvider($listingProvider);

        $this->ormPurger = new ORMPurger($this->doctrine->getManager(), $this->excludedTables);
        // $this->ormPurger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);

        $store = new FlockStore();
        $this->lockFactory = new LockFactory($store);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lock = $this->lockFactory->createLock('orm-purger');

        if ($lock->acquire()) {

            $output->writeln('Verifying database config…');
            $this->handleCraueConfig($input, $output);

            $output->writeln('Purging database…');
            $this->ormPurger->purge();

            $output->writeln('Resetting sequences…');
            $this->resetSequences();

            $output->writeln('Creating super users…');
            foreach (self::$users as $username => $params) {
                $this->createUser($username, $params);
            }

            $output->writeln('Creating users…');
            for ($i = 1; $i <= 50; $i++) {
                $username = "user_{$i}";
                $user = $this->createUser($username, ['password' => $username]);
                $user->addAddress($this->faker->randomAddress);
            }
            $this->doctrine->getManagerForClass(Entity\User::class)->flush();

            $output->writeln('Creating businesses…');
            $this->createBusinesses($output);

            $output->writeln('Removing data from Redis…');
            $keys = $this->redis->keys('*');
            foreach ($keys as $key) {
                $this->redis->del($key);
            }

            $lock->release();
        }


        return 0;
    }

    private function createUser($username, array $params = [])
    {
        $user = $this->userManipulator->create($username, $params['password'], "{$username}@astrada.dev", true, false);
        if (isset($params['roles'])) {
            foreach ($params['roles'] as $role) {
                $this->userManipulator->addRole($username, $role);
            }
        }

        return $user;
    }

    private function createBusiness(Entity\Address $address)
    {

        $business = new Entity\LocalBusiness();

        $phoneNumber = $this->phoneNumberUtil->getExampleNumber(strtoupper($this->country));

        $business->setEnabled(true);
        $business->setTelephone($phoneNumber);
        $business->setAddress($address);
        $business->setName($this->faker->businessName);
        $business->addOpeningHour('Mo-Fr ' . $this->createRandomTimeRange('09:30', '14:30'));
        $business->addOpeningHour('Mo-Fr ' . $this->createRandomTimeRange('19:30', '23:30'));
        $business->addOpeningHour('Sa-Su ' . $this->createRandomTimeRange('08:30', '15:30'));
        $business->addOpeningHour('Sa-Su ' . $this->createRandomTimeRange('19:00', '01:30'));

        foreach ($business->getFulfillmentMethods() as $fulfillmentMethod) {
            $fulfillmentMethod->setMinimumAmount(1500);
        }


        $listings = $this->createListings(rand(12, 50));

        foreach ($listings as $listing){
            $business->addListing($listing);
        }

        return $business;
    }

    private function createBusinesses(OutputInterface $output)
    {


        $em = $this->doctrine->getManagerForClass(Entity\LocalBusiness::class);

        for ($i = 1; $i <= 50; $i++) {

            $business = $this->createBusiness($this->faker->randomAddress);

            $em->persist($business);

            $username = "bis_{$i}";
            $user = $this->createUser($username, [
                'password' => $username,
                'roles' => ['ROLE_BUSINESS']
            ]);
            $user->addBusiness($business);

            if (($i % $this->batchSize) === 0) {

                $output->writeln('Flushing data…');

                $em->flush();
                $em->clear();

            }
        }

        $em->flush();
    }

    private function createListing(Entity\Address $address)
    {
        $uaListing = new Entity\ListingTranslation();
        $uaListing->setLocale('ua');
        $uaListing->setTitle($this->faker->listingName);

        $listing = new Entity\Listing();

        $listing->addTranslation($uaListing);

        $listing->setStatus(Entity\Listing::STATUS_PUBLISHED);

        $listing->setCertified(1);
        $listing->setAddress($address);


        return $listing;
    }

    private function createListings($max=50)
    {
        $listings = [];
//        $em = $this->doctrine->getManagerForClass(Entity\Listing::class);

        for ($i = 1; $i <= $max; $i++) {

            $listing = $this->createListing($this->faker->randomAddress);
            $listings[] = $listing;
//            $em->persist($listing);
//
//
//            if (($i % $this->batchSize) === 0) {
//
//                $em->flush();
//                $em->clear();
//
//            }
        }

//        $em->flush();

        return $listings;
    }

    private function createServiceTaxon($appetizers, $dishes, $desserts)
    {
        $menu = $this->taxonFactory->createNew();

        $menu->setCode($this->faker->uuid);
        $menu->setSlug($this->faker->uuid);
        $menu->setName('Default');

        $appetizersTaxon = $this->taxonFactory->createNew();
        $appetizersTaxon->setCode($this->faker->uuid);
        $appetizersTaxon->setSlug($this->faker->uuid);
        $appetizersTaxon->setName('Entrées');
        foreach ($appetizers as $product) {
            $appetizersTaxon->addProduct($product);
        }

        $dishesTaxon = $this->taxonFactory->createNew();
        $dishesTaxon->setCode($this->faker->uuid);
        $dishesTaxon->setSlug($this->faker->uuid);
        $dishesTaxon->setName('Plats');
        foreach ($dishes as $product) {
            $dishesTaxon->addProduct($product);
        }

        $dessertsTaxon = $this->taxonFactory->createNew();
        $dessertsTaxon->setCode($this->faker->uuid);
        $dessertsTaxon->setSlug($this->faker->uuid);
        $dessertsTaxon->setName('Desserts');
        foreach ($desserts as $product) {
            $dessertsTaxon->addProduct($product);
        }

        $menu->addChild($appetizersTaxon);
        $menu->addChild($dishesTaxon);
        $menu->addChild($dessertsTaxon);

        return $menu;
    }

    private function createWells(TaxCategoryInterface $taxCategory)
    {
        $products = [];

        for ($i = 0; $i < 5; $i++) {
            $appetizer = $this->loadFixtures(__DIR__ . '/Resources/appetizer.yml', [
                'taxCategory' => $taxCategory,
            ], [
                'currentLocale' => $this->defaultLocale,
            ]);

            $appetizer['variant']->setName($appetizer['product']->getName());

            $products[] = $appetizer['product'];
        }

        return $products;
    }

    private function loadFixtures($filename, array $objects = [], $parameters = [])
    {
        return $this->fixturesLoader->load(
            [$filename],
            $parameters,
            $objects,
            PurgeMode::createNoPurgeMode()
        );
    }

    private function createCraueConfigSetting($name, $value, $section = 'general')
    {
        $className = $this->configEntityName;

        $setting = new $className();

        $setting->setName($name);
        $setting->setValue($value);
        $setting->setSection($section);

        return $setting;
    }

    private function createRandomTimeRange($min, $max)
    {
        [$closingHour, $closingMinute] = explode(':', $max);
        [$openingHour, $openingMinute] = explode(':', $min);

        $closing = new \DateTime();
        $closing->setTime($closingHour, $closingMinute);

        $opening = new \DateTime();
        $opening->setTime($openingHour, $openingMinute);

        $increment = mt_rand(0, 5) * 15;
        $decrement = mt_rand(0, 5) * 15;

        $opening->modify("+{$increment} minutes");
        $closing->modify("-{$decrement} minutes");

        return sprintf('%s-%s', $opening->format('H:i'), $closing->format('H:i'));
    }

    private function handleCraueConfig(InputInterface $input, OutputInterface $output)
    {
        $className = $this->configEntityName;
        $em = $this->doctrine->getManagerForClass($className);

        try {
            $mapCenterValue = $this->craueConfig->get('latlng');
        } catch (\RuntimeException $e) {
            $mapCenterValue = implode(',', self::$kievUkraineCoords);
            $mapCenter = $this->createCraueConfigSetting('latlng', $mapCenterValue);
            $em->persist($mapCenter);
        }

        try {
            $this->craueConfig->get('brand_name');
        } catch (\RuntimeException $e) {
            $brandName = $this->createCraueConfigSetting('brand_name', 'Astrada');
            $em->persist($brandName);
        }

        $em->flush();

        // We create a custom geocoder chain using free services

        $stack = HandlerStack::create();
        $stack->push(RateLimiterMiddleware::perSecond(2));

        $httpClient  = new GuzzleClient(['handler' => $stack, 'timeout' => 30.0]);
        $httpAdapter = new Client($httpClient);

        $providers = [];

        if ('ru' === $this->country) {
            $providers[] = AddokProvider::withBANServer($httpAdapter);
        }

        // Make sure we use a language supported by Photon
        // "language es is not supported, supported languages are: default, en, fr, de, it"
        $geocoderLocale = in_array($this->defaultLocale, ['en', 'fr', 'de', 'it']) ? $this->defaultLocale : 'en';

        $providers[] = PhotonProvider::withKomootServer($httpAdapter);

        $statefulGeocoder =
            new StatefulGeocoder(new ChainProvider($providers), $geocoderLocale);

        $this->geocoder->setGeocoder($statefulGeocoder);

        $addressProvider = new AddressProvider($this->faker, $this->geocoder, new Coordinate($mapCenterValue));

        $this->faker->addProvider($addressProvider);
    }

    private function resetSequences()
    {
        $connection = $this->doctrine->getConnection();
        $rows = $connection->fetchAll('SELECT sequence_name FROM information_schema.sequences');
        foreach ($rows as $row) {

            $sequenceName = $row['sequence_name'];
            $tableName = str_replace('_id_seq', '', $sequenceName);

            if (in_array($tableName, $this->excludedTables)) {
                continue;
            }

            try {
                $connection->executeQuery(sprintf('ALTER SEQUENCE %s RESTART WITH 1', $row['sequence_name']));
            } catch (TableNotFoundException $e) {
                // We don't care
            }
        }
    }
}