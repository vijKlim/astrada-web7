<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *   shortName="BusinessListingList",
 *   attributes={
 *     "denormalization_context"={"groups"={ "listing_public"}},
 *     "normalization_context"={"groups"={"listing_public"}}
 *   },
 *   collectionOperations={
 *     "get"={
 *       "method"="GET",
 *       "pagination_enabled"=false,
 *       "normalization_context"={"groups"={"listing_public"}}
 *     },
 *   },
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "normalization_context"={"groups"={"listing_public"}},
 *       "security"="is_granted('view', object)"
 *     },
 *   }
 * )
 */
class BusinessListingList
{
    /**
     * @ApiProperty(identifier=true)
     */
    public $id;
    /**
     * @var LocalBusiness $business
     * @Groups({"listing_public"})
     */
    private $business;
    /**
     * @var Listing[]
     * @Groups({"listing_public"})
     */
    private $items = [];

    public function __construct(LocalBusiness $business)
    {
        $this->business = $business;
        $this->id = $this->business->getId();
    }

    /**
     * @return LocalBusiness
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @return Listing[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem(Listing $listing)
    {
        $this->items[] = $listing;
    }

}