<?php

namespace App\Twig;

use App\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Node\Node;
use Symfony\Component\ExpressionLanguage\ParsedExpression;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Twig\Extension\RuntimeExtensionInterface;

class ExpressionLanguageRuntime implements RuntimeExtensionInterface
{
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function parseExpression($expression): ParsedExpression
    {
        return $this->expressionLanguage->parse($expression, [
            'distance',
            'weight',
            'drillingKits',
            'pipeDiameters',
            'vehicle',
            'pickup',
            'dropoff',
            'packages',
            'order',
        ]);
    }
}
