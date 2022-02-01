<?php


namespace App\Entity;


use App\Entity\LocalBusiness\ShippingOptionsInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Vendor
    implements ShippingOptionsInterface
{
    private $id;
    /**
     * @var LocalBusiness
     */
    private $business;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param mixed $business
     *
     * @return self
     */
    public function setBusiness($business)
    {
        $this->business = $business;

        return $this;
    }

    public function getAddress()
    {

        return $this->business->getAddress();
    }

    public function getOpeningHours($method = 'delivery')
    {
        return $this->business->getOpeningHours($method);
    }

    public function hasClosingRuleFor(\DateTime $date = null, \DateTime $now = null): bool
    {
        return $this->business->hasClosingRuleFor($date, $now);
    }

    public function isFulfillmentMethodEnabled($method)
    {
        return $this->business->isFulfillmentMethodEnabled($method);
    }

    public function getFulfillmentMethod(string $method)
    {
        return $this->business->getFulfillmentMethod($method);
    }

    public function getFulfillmentMethods()
    {
        return $this->business->getFulfillmentMethods();
    }

    public function getShippingOptionsDays()
    {
        return $this->business->getShippingOptionsDays();
    }

    public function getClosingRules()
    {
        return $this->business->getClosingRules();
    }



    public function getName()
    {
        return $this->business->getName();
    }

    public function canDeliverAddress(Address $address, $distance, ExpressionLanguage $language = null)
    {
        return $this->business->canDeliverAddress($address, $distance, $language);
    }

    public function getDeliveryPerimeterExpression()
    {


        return $this->business->getDeliveryPerimeterExpression();
    }

    public function getOwners(): Collection
    {


        return $this->business->getOwners();
    }




    public static function withBusiness(LocalBusiness $business)
    {
        $vendor = new self();
        $vendor->setBusiness($business);

        return $vendor;
    }


}