<?php


namespace App\Action\Listing;


use App\Api\Resource\LisitngPrice;
use App\Entity\Listing;
use App\Service\ListingManager;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CalculatePrice
{
    public function __construct(
        ListingManager $listingManager,
        CurrencyContextInterface $currencyContext)
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
        $amount_trans_cost = 0;
        $amount_well_cost = 0;
        foreach ($business->getListingPricingRuleSets() as $pricingRuleSet){
            $amount_trans_cost = $this->listingManager->getPrice($data, Listing\ListingPricingRule::TYPE_TRANSPORTATION_COST, $pricingRuleSet);
            $amount_well_cost = $this->listingManager->getPrice($data, Listing\ListingPricingRule::TYPE_WELL_COST, $pricingRuleSet);
        }


        $listingPrice = new LisitngPrice(
            (int)$amount_trans_cost,
            (int)$amount_well_cost,
            $this->currencyContext->getCurrencyCode()
        );

        return $listingPrice;
    }
}