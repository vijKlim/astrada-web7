<?php


namespace App\Form\ListingFlow;

use App\Entity\Listing;
use App\Entity\LocalBusiness;
use App\Factory\ListingFactory;
use Symfony\Component\Validator\Constraints as Assert;

class CreateListing
{
    protected $listingFactory;
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

    public function __construct(ListingFactory $listingFactory) {
        $this->listingFactory = $listingFactory;
        $this->business = new LocalBusiness();
        $this->listing  = $this->listingFactory
            ->createNew();
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