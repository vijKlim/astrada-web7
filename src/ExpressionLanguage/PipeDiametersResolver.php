<?php


namespace App\ExpressionLanguage;


use App\Entity\Listing;

class PipeDiametersResolver
{
    private $listing;

    public function __construct(Listing $listing)
    {
        $this->listing = $listing;
    }

    public function containsAtLeastOne($name): bool
    {
        if ($this->listing->getWelldesign()->getPipeDiameter() === $name) {
            return true;
        }

        return false;
    }
}