<?php


namespace App\ExpressionLanguage;


use App\Entity\Listing;

class DrillingKitsResolver
{
    private $listing;

    public function __construct(Listing $listing)
    {
        $this->listing = $listing;
    }

    public function containsAtLeastOne($name): bool
    {
        if ($this->listing->getWelldesign()->getDrillingKit() === $name) {
            return true;
        }

        return false;
    }
}