<?php


namespace App\Serializer;


use ApiPlatform\Core\JsonLd\Serializer\ItemNormalizer;
use App\Entity\BusinessListingList;
use App\Entity\Listing;
use App\Utils\PriceFormatter;
use Cocur\Slugify\SlugifyInterface;
use Liip\ImagineBundle\Service\FilterService;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class BusinessListingListNormalizer
    implements NormalizerInterface, DenormalizerInterface
{
    private $normalizer;
    private $urlGenerator;
    private $requestStack;
    private $uploaderHelper;
    private $currencyContext;
    private $priceFormatter;
    private $slugify;
    private $locale;

    public function __construct(
        ItemNormalizer $normalizer,
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        UploaderHelper $uploaderHelper,
        SlugifyInterface $slugify,
        FilterService $imagineFilter,
        string $locale)
    {
        $this->normalizer = $normalizer;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->uploaderHelper = $uploaderHelper;
        $this->slugify = $slugify;
        $this->imagineFilter = $imagineFilter;
        $this->locale = $locale;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        /** @var BusinessListingList $object */
        $data['businesId'] = $object->getBusiness()->getId();
        $data['businesName'] = $object->getBusiness()->getName();

        $imagePath = $this->uploaderHelper->asset($object->getBusiness(), 'imageFile');
        if (empty($imagePath)) {
            $imagePath = '/img/business/default.png';
            $request = $this->requestStack->getCurrentRequest();
            if ($request) {
                $data['image'] = $request->getUriForPath($imagePath);
            }
        } else {
            $data['image'] = $this->imagineFilter->getUrlOfFilteredImage($imagePath, 'business_thumbnail');
        }

        foreach ($object->getItems() as $listing) {
            $data['items'][] =[
                'id' => $listing->getId(),
                'title' => $listing->getTitle(),
                'address' => $listing->getAddress()
            ];
        }

        return $data;
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