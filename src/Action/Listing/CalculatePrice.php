<?php


namespace App\Action\Listing;


use App\Api\Resource\LisitngPrice;
use App\Entity\Listing;
use App\Service\ListingManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CalculatePrice
{
    public function __construct(
        ListingManager $listingManager,
        CurrencyContextInterface $currencyContext,
        string $state)
    {
        $this->listingManager = $listingManager;
        $this->currencyContext = $currencyContext;
    }

    public function __invoke(Listing $data, Request $request)
    {
        $business = $data->getBusiness();
//        if (null === $business) {
//            $business = $this->storeExtractor->extractStore();
//        }

        $amount = $this->listingManager->getPrice($data, $business->getListingPricingRuleSets());

        if (null === $amount) {
            throw new BadRequestHttpException('Price could not be calculated');
        }

        $listingPrice = new LisitngPrice(
            $amount,
            $this->currencyContext->getCurrencyCode()
        );

        return $listingPrice;
    }
}