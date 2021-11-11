<?php


namespace App\Api\Dto;


use App\Entity\LocalBusiness;
use App\Entity\Welldesign;
use Symfony\Component\Serializer\Annotation\Groups;

class ListingInput
{
    /**
     * @var LocalBusiness|null
     * @Groups({"pricing_listings"})
     */
    public $business;
    /**
     * @var Welldesign
     * @Groups({"pricing_listings"})
     */
    public $welldesign;
}