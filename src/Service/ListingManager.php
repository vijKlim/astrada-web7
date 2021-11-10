<?php


namespace App\Service;

use App\Entity\Listing;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ListingManager
{
    private $expressionLanguage;
    private $routing;
    private $orderTimeHelper;
    private $storeExtractor;
    private $orderTimelineCalculator;
    private $logger;

    public function __construct(
        ExpressionLanguage $expressionLanguage,
        RoutingInterface $routing,
        LoggerInterface $logger = null)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->routing = $routing;
        $this->logger = $logger ?? new NullLogger();
    }

    public function getPrice(Listing $listing, Listing\ListingPricingRuleSet $ruleSet)
    {
        if ($ruleSet->getStrategy() === 'find') {

            foreach ($ruleSet->getRules() as $rule) {
                if ($rule->matches($listing, $this->expressionLanguage)) {
                    $this->logger->info(sprintf('Matched rule "%s"', $rule->getExpression()));

                    return $rule->evaluatePrice($listing, $this->expressionLanguage);
                }
            }

            return null;
        }

        if ($ruleSet->getStrategy() === 'map') {

            $totalPrice = 0;
            $matchedAtLeastOne = false;

            foreach ($ruleSet->getRules() as $rule) {
                if ($rule->matches($listing, $this->expressionLanguage)) {
                    $this->logger->info(sprintf('Matched rule "%s"', $rule->getExpression()));

                    $price = $rule->evaluatePrice($listing, $this->expressionLanguage);
                    $totalPrice += $price;

                    $matchedAtLeastOne = true;
                }
            }

            if ($matchedAtLeastOne) {

                return $totalPrice;
            }
        }

        return null;
    }
}