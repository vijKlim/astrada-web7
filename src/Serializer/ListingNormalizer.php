<?php


namespace App\Serializer;

use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use App\Entity\Listing;
use App\Entity\ListingImage;
use App\Utils\PriceFormatter;
use Cocur\Slugify\SlugifyInterface;
use Liip\ImagineBundle\Service\FilterService;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class ListingNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private $normalizer;
    private $urlGenerator;
    private $requestStack;
    private $uploaderHelper;
    private $imagineFilter;
    private $currencyContext;
    private $priceFormatter;
    private $slugify;
    private $locale;

    public function __construct(
        ItemNormalizer $normalizer,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        UploaderHelper $uploaderHelper,
        CurrencyContextInterface $currencyContext,
        PriceFormatter $priceFormatter,
        SlugifyInterface $slugify,
        FilterService $imagineFilter,
        string $locale)
    {
        $this->normalizer = $normalizer;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->uploaderHelper = $uploaderHelper;
        $this->currencyContext = $currencyContext;
        $this->priceFormatter = $priceFormatter;
        $this->slugify = $slugify;
        $this->imagineFilter = $imagineFilter;
        $this->locale = $locale;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

         /** @var Listing $object */
        $data['title'] = $object->getTitle();
        $data['description'] = $object->getDescription();
        $urlDefaultParameters = [
            'id' => $object->getId(),
            'slug' => $this->slugify->slugify($object->getTitle())
        ];
        $data['href'] = $this->urlGenerator->generate('listing',$urlDefaultParameters,UrlGeneratorInterface::ABSOLUTE_PATH);

        $main_image = null;
        if(!empty($object->getImages())){
            foreach ($object->getImages() as $k=>$imageObject){
                $imageUrl = $this->imageNormalizer($imageObject);
                if(!$main_image){
                    $data['image'] = $imageUrl;
                }
                $data['gallery'][] = $imageUrl;
            }
        }else{
            $data['image'] = '//via.placeholder.com/512.jpg';
            $data['gallery'] = [];
        }

        return $data;
    }

    private function imageNormalizer(ListingImage $imageObject)
    {
        $imagePath = $this->uploaderHelper->asset($imageObject, 'imageFile');

        if (null !== $imagePath) {
            try{
                $imageUrl  = $this->imagineFilter->getUrlOfFilteredImage($imagePath, 'listing_thumbnail');
            }catch (\Exception $e){
                $imageUrl = '//via.placeholder.com/512.jpg';
            }

        }else{
            $imageUrl = '//via.placeholder.com/512.jpg';
        }
        return $imageUrl;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format) && $data instanceof Listing;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $this->normalizer->supportsDenormalization($data, $type, $format) && $type === Listing::class;
    }
}