<?php

namespace App\Entity\Listing;


use App\Entity\Listing;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class ListingPricingRule
{
    const TYPE_TRANSPORTATION_COST = 'transportation_cost';
    const TYPE_WELL_COST = 'well_cost';

    /**
     * @var int
     */
    protected $id;

    /**
     * @Groups({"original_rules"})
     * @Assert\Type(type="string")
     */
    protected $type;

    /**
     * @Groups({"original_rules"})
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     */
    protected $expression;

    /**
     * @Groups({"original_rules"})
     * @Assert\Type(type="string")
     */
    protected $price;

    /**
     * @Groups({"original_rules"})
     */
    protected $position;

    protected $ruleSet;

    /**
     * Gets id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function setExpression($expression)
    {
        $this->expression = $expression;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }


    public function getRuleSet()
    {
        return $this->ruleSet;
    }

    public function setRuleSet(ListingPricingRuleSet $ruleSet)
    {
        $this->ruleSet = $ruleSet;

        return $this;
    }

    public function evaluatePrice(Listing $listing, ExpressionLanguage $language = null)
    {
        if (null === $language) {
            $language = new ExpressionLanguage();
        }

        return $language->evaluate($this->getPrice(), Listing::toExpressionLanguageValues($listing));
    }

    public function matches(Listing $listing, ExpressionLanguage $language = null)
    {
        if (null === $language) {
            $language = new ExpressionLanguage();
        }

        return $language->evaluate($this->getExpression(), Listing::toExpressionLanguageValues($listing));
    }
}