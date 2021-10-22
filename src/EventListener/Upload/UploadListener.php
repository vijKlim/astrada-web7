<?php

namespace App\EventListener\Upload;

use ApiPlatform\Core\Api\IriConverterInterface;
use App\Entity\Listing;
use App\Entity\LocalBusiness;
use App\Entity\Sylius\Product;
use App\Entity\Sylius\ProductImage;
use App\Service\SettingsManager;
use App\Spreadsheet\ProductSpreadsheetParser;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\Hashids;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Serializer\SerializerInterface;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

final class UploadListener
{
    private $entityManager;
    private $mappingFactory;
    private $uploadHandler;
    private $settingsManager;
    private $messageBus;
    private $productSpreadsheetParser;
    private $secret;
    private $isDemo;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        PropertyMappingFactory $mappingFactory,
        UploadHandler $uploadHandler,
        SettingsManager $settingsManager,
        MessageBusInterface $messageBus,
        ProductSpreadsheetParser $productSpreadsheetParser,
        SerializerInterface $serializer,
        IriConverterInterface $iriConverter,
        CacheInterface $projectCache,
        string $secret,
        bool $isDemo,
        LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->mappingFactory = $mappingFactory;
        $this->uploadHandler = $uploadHandler;
        $this->settingsManager = $settingsManager;
        $this->messageBus = $messageBus;
        $this->productSpreadsheetParser = $productSpreadsheetParser;
        $this->serializer = $serializer;
        $this->iriConverter = $iriConverter;
        $this->projectCache = $projectCache;
        $this->secret = $secret;
        $this->isDemo = $isDemo;
        $this->logger = $logger;
    }

    public function onUpload(PostPersistEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $file = $event->getFile();

//        if ('products' === $event->getType()) {
//
//            try {
//
//                $restaurant = $this->iriConverter->getItemFromIri($request->get('restaurant'));
//
//                $products = $this->productSpreadsheetParser->parse($file);
//                foreach ($products as $product) {
//                    $restaurant->addProduct($product);
//                }
//
//                $this->entityManager->flush();
//
//                $file->getFilesystem()->delete($file->getPathname());
//
//            } catch (\Exception $e) {
//
//                $file->getFilesystem()->delete($file->getPathname());
//
//                throw new UploadException($e->getMessage());
//            }
//
//            // $response['products'] = $this->serializer->normalize($products, 'json', ['iri' => '']);
//
//            return $response;
//        }

        if ('banner' === $event->getType()) {
            return $this->onBannerUpload($event);
        }

        $type = $request->get('type');

        if ($type === 'logo') {
            return $this->onLogoUpload($event);
        }



        if ($type === 'business') {
            $object = $this->entityManager->getRepository(LocalBusiness::class)->find(
                $request->get('id')
            );
            // Remove previous file
            $this->uploadHandler->remove($object, 'imageFile');
        }elseif ($type === 'listing') {
            $object = $this->entityManager->getRepository(Listing::class)->find(
                $request->get('id')
            );
            // Remove previous file
            $this->uploadHandler->remove($object, 'imageFile');
        } elseif ($type === 'product') {
            $product = $this->entityManager->getRepository(Product::class)->find(
                $request->get('id')
            );

            $object = new ProductImage();
            $object->setRatio($request->get('ratio', '1:1'));

            $product->addImage($object);

        } else {
            return;
        }

        // Update image_name column in database
        $object->setImageName($file->getBasename());
        $this->entityManager->flush();

        // Invoke VichUploaderBundle's directory namer
        $propertyMapping = $this->mappingFactory->fromField($object, 'imageFile');
        $directoryNamer = $propertyMapping->getDirectoryNamer();

        $directoryName = $directoryNamer->directoryName($object, $propertyMapping);

        $file->getFilesystem()->rename(
            $file->getPath(),
            sprintf('%s/%s', $directoryName, $file->getBasename())
        );
    }

    private function onLogoUpload(PostPersistEvent $event)
    {
        $file = $event->getFile();

        if ($this->isDemo) {
            throw new UploadException('Company logo can\'t be changed in demo mode');
        }

        $this->settingsManager->set('company_logo', $file->getBasename());
        $this->settingsManager->flush();

        $this->projectCache->delete('content.company_logo.base_64');
    }


    private function onBannerUpload(PostPersistEvent $event)
    {
        $file = $event->getFile();

        if ($this->isDemo) {
            throw new UploadException('Banner can\'t be changed in demo mode');
        }

        $this->projectCache->delete('banner_svg_stat');
        $this->projectCache->delete('banner_svg');
    }
}
