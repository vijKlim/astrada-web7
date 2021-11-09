<?php

namespace App\Entity\Listing;

use App\Entity\LocalBusiness;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraints as Assert;

class ListingPricingRuleSet
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @Assert\Valid()
     */
    protected $rules;

    protected $name;

    protected $strategy = 'find';

    /**
     * @var LocalBusiness
     */
    protected $business;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
    }

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * @return LocalBusiness|null
     */
    public function getBusiness(): ?LocalBusiness
    {
        return $this->business;
    }

    /**
     * @param LocalBusiness|null $business
     */
    public function setBusiness(?LocalBusiness $business)
    {
        $this->business = $business;
    }

    /**
     * @return mixed
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param mixed $strategy
     *
     * @return self
     */
    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;

        return $this;
    }
}