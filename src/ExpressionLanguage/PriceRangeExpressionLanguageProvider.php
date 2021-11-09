<?php


namespace App\ExpressionLanguage;


use App\Entity\Address;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class PriceRangeExpressionLanguageProvider
    implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        $compiler = function (Address $address, $zoneName) {
            // FIXME Need to test compilation
        };

        $evaluator = function ($arguments, $distance, $price, $size, $over) {

            return (int) ceil(($distance - $over) / $size) * $price;
        };

        return array(
            new ExpressionFunction('price_range', $compiler, $evaluator),
        );
    }
}