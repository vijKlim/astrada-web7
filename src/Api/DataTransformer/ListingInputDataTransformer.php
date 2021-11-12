<?php


namespace App\Api\DataTransformer;

use App\Api\Resource\LisitngPrice;
use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Entity\Listing;
use App\Entity\ListingRepository;
use App\Entity\LocalBusiness;
use App\Entity\Welldesign;
use Sylius\Component\Resource\Factory\FactoryInterface;

class ListingInputDataTransformer implements DataTransformerInterface
{
    private $listingFactory;

    public function __construct(
        FactoryInterface $listingFactory
    )
    {
        $this->listingFactory = $listingFactory;
    }

    public function transform($data, string $to, array $context = [])
    {
        /** @var Listing $listing */
        $listing = $this->listingFactory
            ->createNew();

        if ($data->business && $data->business instanceof LocalBusiness) {
            $listing->setBusiness($data->business);
        }

        if($data->welldesign){

            $welldesign = new Welldesign();
            $welldesign->setPipeDiameter($data->welldesign['pipeDiameter']);
            $welldesign->setDrillingKit($data->welldesign['drillingKit']);
            $welldesign->setDepthFrom($data->welldesign['depthFrom']);
            $welldesign->setDepthTo($data->welldesign['depthTo']);
            $listing->setWelldesign($welldesign);

        }
        return $listing;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        if ($data instanceof LisitngPrice) {
            return false;
        }

        return LisitngPrice::class === $to && null !== ($context['input']['class'] ?? null);
    }
}