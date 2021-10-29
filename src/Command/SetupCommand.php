<?php


namespace App\Command;


use App\Entity\ListingCategory;
use App\Entity\ListingCategoryTranslation;
use App\Entity\Sylius\Taxon;
use App\Entity\Sylius\TaxonRepository;
use App\Enum\PipeDiameter;
use App\Enum\Product;
use App\Enum\Service;
use App\Enum\VehicleType;
use App\Factory\ListingCategoryFactory;
use App\Service\SettingsManager;
use App\Sylius\Product\ProductInterface;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;
use Stripe;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Factory\ProductVariantFactoryInterface;
use Sylius\Component\Product\Model\ProductAttribute;
use Sylius\Component\Product\Repository\ProductRepositoryInterface;
use Sylius\Component\Attribute\AttributeType\IntegerAttributeType;
use Sylius\Component\Attribute\AttributeType\TextAttributeType;
use Sylius\Component\Attribute\Model\AttributeValueInterface;
use Sylius\Component\Channel\Factory\ChannelFactoryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Payment\Model\PaymentMethod;
use Sylius\Component\Promotion\Model\Promotion;
use Sylius\Component\Promotion\Model\PromotionAction;
use Sylius\Component\Promotion\Repository\PromotionRepositoryInterface;
use Sylius\Component\Taxation\Repository\TaxCategoryRepositoryInterface;
use Sylius\Component\Taxonomy\Factory\TaxonFactoryInterface;
use Sylius\Component\Taxonomy\Generator\TaxonSlugGeneratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SetupCommand extends Command
{

    private $productRepository;
    private $productManager;
    private $productFactory;
    private $variantFactory;
    private $taxonFactory;
    private $taxonRepository;
    private $productAttributeRepository;
    private $productAttributeManager;
    private $productAttributeValueFactory;

    private $localeRepository;
    private $localeFactory;

    private $currencyRepository;
    private $currencyFactory;

    private $slugify;

    private $entityManager;
    private $listingCategoryFactory;

    private $locale;

    private $locales = [
        'en',
        'ua',
        'ru'
    ];

    private $currencies = [
        'EUR',
        'USD',
        'UAH',
    ];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductFactoryInterface $productFactory,
        EntityManagerInterface $productManager,
        ProductVariantFactoryInterface $variantFactory,
        TaxonFactoryInterface $taxonFactory,
        TaxonRepository $taxonRepository,
        RepositoryInterface $productAttributeRepository,
        EntityManagerInterface $productAttributeManager,
        FactoryInterface $productAttributeValueFactory,
        RepositoryInterface $localeRepository,
        FactoryInterface $localeFactory,
        RepositoryInterface $currencyRepository,
        FactoryInterface $currencyFactory,
        SlugifyInterface $slugify,
        TranslatorInterface $translator,
        SettingsManager $settingsManager,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager,
        ListingCategoryFactory $listingCategoryFactory,

        string $locale,
        string $country
    )
    {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
        $this->productManager = $productManager;
        $this->variantFactory = $variantFactory;
        $this->taxonFactory = $taxonFactory;
        $this->taxonRepository = $taxonRepository;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->productAttributeManager = $productAttributeManager;
        $this->productAttributeValueFactory = $productAttributeValueFactory;

        $this->entityManager = $entityManager;
        $this->listingCategoryFactory = $listingCategoryFactory;


        $this->localeRepository = $localeRepository;
        $this->localeFactory = $localeFactory;

        $this->currencyRepository = $currencyRepository;
        $this->currencyFactory = $currencyFactory;

        $this->slugify = $slugify;

        $this->translator = $translator;

        $this->settingsManager = $settingsManager;

        $this->urlGenerator = $urlGenerator;

        $this->locale = $locale;
        $this->country = $country;
        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('astrada:setup')
            ->setDescription('Setups some basic stuff.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
//        $output->writeln('<info>Setting up Astrada</info>');
//
//        $output->writeln('<info>Checking Sylius locales are present…</info>');
//        foreach ($this->locales as $locale){
//            $this->createSyliusLocale($locale, $output);
//        }
//
//        $output->writeln('<info>Checking Sylius currencies are present…</info>');
//        foreach ($this->currencies as $currencyCode) {
//            $this->createSyliusCurrency($currencyCode, $output);
//        }
//
////        $output->writeln('<info>Checking Sylius welldesigns attributes are present…</info>');
////        $this->createWelldesignsAttributes($output);
//
//        $output->writeln('<info>Checking Sylius listing services are present…</info>');
//        $services = $this->createListingServices($output);
//
////        $output->writeln('<info>Checking Sylius welldesigns products are present…</info>');
////        $this->createWelldesignsProducts($services, $output);

        $output->writeln('<info>Checking Sylius listing categories are present…</info>');
        $listing_categories = $this->createListingCategories($output);
        return 0;
    }

    public function createSyliusLocale($code, OutputInterface $output)
    {
        $locale = $this->localeRepository->findOneByCode($code);

        if(null !== $locale){
            $output->writeln(sprintf('Sylius locale "%s" already exists', $code));
            return;
        }

        $locale = $this->localeFactory->createNew();
        $locale->setCode($code);

        $this->localeRepository->add($locale);

        $output->writeln(sprintf('Sylius locale "%s" created', $code));
    }

    private function createSyliusCurrency($code, OutputInterface $output)
    {
        $currency = $this->currencyRepository->findOneByCode($code);

        if (null !== $currency) {
            $output->writeln(sprintf('Sylius currency "%s" already exists', $code));
            return;
        }

        $currency = $this->currencyFactory->createNew();
        $currency->setCode($code);

        $this->currencyRepository->add($currency);

        $output->writeln(sprintf('Sylius currency "%s" created', $code));
    }

    private function createListingServices(OutputInterface $output)
    {
        $serviceTaxons = new ArrayCollection();
        foreach (Service::values() as $service){
            $service_code = $service->getKey();

            $serviceTaxon = $this->taxonRepository->findOneByCode($service_code);

            if (null === $serviceTaxon) {

                $serviceTaxon = $this->taxonFactory->createNew();

                $uuid = $service_code;
                $name = $this->translator->trans('service.'.$service_code, [], 'messages', 'ua');
                $serviceTaxon->setCode($uuid);
                $serviceTaxon->setSlug($uuid);
                $serviceTaxon->setName($name);

                $this->taxonRepository->add($serviceTaxon);
                $output->writeln('Creating serviceTaxon « '.$service_code.' »');
                $serviceTaxons->add($serviceTaxon);
            } else {
                $output->writeln('ServiceTaxon « '.$service_code.' » already exists');
            }
        }
        return $serviceTaxons;
    }

    private function createWelldesignsAttributes(OutputInterface $output)
    {
        $this->createAttribute('PIPE_DIAMETER', 'form.welldesign.pipeDiameter.label',TextAttributeType::TYPE, AttributeValueInterface::STORAGE_TEXT, $output);
        $this->createAttribute('DEPTH_FROM', 'form.welldesign.depthFrom.label',IntegerAttributeType::TYPE, AttributeValueInterface::STORAGE_INTEGER, $output);
        $this->createAttribute('DEPTH_TO', 'form.welldesign.depthTo.label',IntegerAttributeType::TYPE, AttributeValueInterface::STORAGE_INTEGER, $output);
        $this->createAttribute('VEHICLE_TYPE', 'form.welldesign.vihicleType.label',TextAttributeType::TYPE, AttributeValueInterface::STORAGE_JSON, $output);
    }

    private function createWelldesignsProducts( ArrayCollection $services, OutputInterface $output)
    {
        /** @var Taxon $service */
        foreach ($services as $service){
            foreach (PipeDiameter::values() as $value)
            {
                $product = $this->createWelldesignProduct($value, $output);
                $service->addProduct($product);
            }
        }

    }

    private function createWelldesignProduct(PipeDiameter $pipeDiameter, OutputInterface $output)
    {
        $product_code = 'WELLDESIGN_'.$pipeDiameter->getKey();

//        var_dump($pipeDiameter->getKey(), $pipeDiameter->getValue());die();
        $product = $this->productRepository->findOneByCode($product_code);

        if (null === $product) {

            $product = $this->productFactory->createNew();
            $product->setCode($product_code);
            $product->setEnabled(true);
            $product->setType(Product::WELL_DESIGN);

            $variant = $this->variantFactory->createForProduct($product);

            $variant->setName($product->getName());
            $variant->setCode(Uuid::uuid4()->toString());
            $variant->setPrice(0);
            $product->addVariant($variant);

            $this->productRepository->add($product);
            $output->writeln('Creating product « '.$product_code.' »');

        } else {
            $output->writeln('Product « '.$product_code.' » already exists');
        }

        $output->writeln('Verifying translations for product « '.$product_code.' »');

        foreach ($this->locales as $locale) {

            $name = $this->translator->trans('welldesign.pipeDiameter.'.$pipeDiameter->getKey(), [], 'messages', $locale);

            $product->setFallbackLocale($locale);
            $translation = $product->getTranslation($locale);

            $translation->setName($name);
            $translation->setSlug($this->slugify->slugify($name));
        }

        $this->createProductAttributeValue($product, 'PIPE_DIAMETER',$pipeDiameter->getKey());
        $this->createProductAttributeValue($product, 'DEPTH_FROM',10);
        $this->createProductAttributeValue($product, 'DEPTH_TO',120);
        $this->createProductAttributeValue($product, 'VEHICLE_TYPE',VehicleType::keys());

        $this->productManager->flush();

        return $product;
    }

    private function createProductAttributeValue(ProductInterface $product, $attributeCode, $attributeDefaultValue)
    {

        foreach ($this->locales as $locale) {

            $attributeValue = $product
                ->getAttributeByCodeAndLocale($attributeCode, $locale);

            if (null === $attributeValue) {
                $attribute =
                    $this->productAttributeRepository->findOneBy(['code' => $attributeCode]);
                $attributeValue =
                    $this->productAttributeValueFactory->createNew();

                $attributeValue->setAttribute($attribute);
                $attributeValue->setLocaleCode($locale);
            }
            if($attributeValue->getType() == AttributeValueInterface::STORAGE_INTEGER){
                $attributeValue->setValue((int)$attributeDefaultValue);
            }else{
                $attributeValue->setValue($attributeDefaultValue);
            }

            $product->addAttribute($attributeValue);
        }

    }

    private function createAttribute($code, $name_t, $type, $storageType, OutputInterface $output)
    {
        $attribute = $this->productAttributeRepository->findOneByCode($code);

        if(null === $attribute) {
            $attribute = new ProductAttribute();
            $attribute->setCode($code);
            $attribute->setType($type);
            $attribute->setStorageType($storageType);

            $this->productAttributeRepository->add($attribute);
            $output->writeln('Creating attribute « '.$code.' »');
        } else {
            $output->writeln('Attribute « '.$code.' » already exists');
        }

        $output->writeln('Verifying translations for attribute « '.$code.' »');

        foreach ($this->locales as $locale) {

            $name = $this->translator->trans($name_t, [], 'messages', $locale);

            $attribute->setFallbackLocale($locale);
            $translation = $attribute->getTranslation($locale);

            $translation->setName($name);
        }

        $this->productAttributeManager->flush();
    }

    private function createListingCategories(OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>%s</comment>', $this->getDescription()));

        foreach ($this->getRootCategories() as $data) {
            $output->writeln(sprintf('Loading <comment>%s</comment> root category', $data['name']));

            $rootTaxon = $this->createOrReplaceRootCategory($data);
            $this->entityManager->persist($rootTaxon);
        }

        $this->entityManager->flush();
        $output->writeln(sprintf('<info>%s root categories successfully loaded</info>', count($this->getRootCategories())));

    }

    /**
     * @param array $data
     *
     * @return ListingCategory
     */
    protected function createOrReplaceRootCategory(array $data)
    {
        /** @var ListingCategory $rootCategory */
        $rootCategory = $this->entityManager->getRepository(ListingCategory::class)->findOneBy(['code' => $data['code']]);

        if (null === $rootCategory) {
            $rootCategory = new ListingCategory();

        }
        $rootCategory->setCode($data['code']);
        foreach ($this->locales as $locale) {

            $tListingCat = new ListingCategoryTranslation();
            $tListingCat->setLocale($locale);
            $tListingCat->setName($data['t'][$locale]);
            $tListingCat->setSlug($this->slugify->slugify($tListingCat->getName()));
            $rootCategory->addTranslation($tListingCat);
        }



        return $rootCategory;
    }


    /**
     * @return array
     */
    protected function getRootCategories()
    {
        return [
            [
                'code' => ListingCategory::CODE_DRILLING_WATER_WELLS,
                'name' => 'Drilling water wells',
                't' => [
                    'en' => 'Drilling water wells',
                    'ua' => 'Буріння свердловин на воду',
                    'ru' => 'Бурение скважин на воду'
                ]
            ],
        ];
    }

}