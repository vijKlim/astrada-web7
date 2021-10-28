<?php


namespace App\Entity;

use App\Entity\ListingAttributeCategory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Comparable;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Taxonomy\Model\Taxon as BaseTaxon;

class ListingCategory
    extends BaseTaxon implements Comparable
{
    private $taxonAttributes;

    public function __construct()
    {
        parent::__construct();

        $this->taxonAttributes = new ArrayCollection();
    }

    public function getTaxonAttributes()
    {
        return $this->taxonAttributes;
    }

    public function setTaxonAttributes(Collection $taxonAttributes)
    {
        $this->taxonAttributes = $taxonAttributes;
    }

    public function addAttribute(AttributeInterface $attribute)
    {
        $attributeTaxon = new ListingAttributeCategory();
        $attributeTaxon->setCategory($this);
        $attributeTaxon->setAttribute($attribute);

        $this->taxonAttributes->add($attributeTaxon);
    }

    public function getAttributes()
    {
        return $this->taxonAttributes->map(function (ListingAttributeCategory $attributeTaxon): AttributeInterface {
            return $attributeTaxon->getAttribute();
        });
    }

    /**
     * {@inheritdoc}
     *
     * @see https://github.com/Sylius/Sylius/issues/10797
     * @see https://github.com/Sylius/Sylius/pull/11329
     * @see https://github.com/Atlantic18/DoctrineExtensions/pull/2185
     */
    public function compareTo($other)
    {
        return $this->code === $other->getCode();
    }
}