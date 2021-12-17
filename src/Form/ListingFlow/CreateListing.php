<?php


namespace App\Form\ListingFlow;

use App\Entity\Listing;
use App\Entity\LocalBusiness;
use Symfony\Component\Validator\Constraints as Assert;

class CreateListing
{
    /**
     * @var LocalBusiness
     * @Assert\Valid
     */
    public $business;

    /**
     * @var Listing
     * @Assert\Valid
     */
    public $listing;

    public function __construct() {
        $this->business = new LocalBusiness();
        $this->listing = new Listing();
    }

    /**
     * @return LocalBusiness
     */
    public function getBusiness(): LocalBusiness
    {
        return $this->business;
    }

    /**
     * @param LocalBusiness $business
     */
    public function setBusiness(LocalBusiness $business): void
    {
        $this->business = $business;
    }

    /**
     * @return Listing
     */
    public function getListing(): Listing
    {
        return $this->listing;
    }

    /**
     * @param Listing $listing
     */
    public function setListing(Listing $listing): void
    {
        $this->listing = $listing;
    }


}