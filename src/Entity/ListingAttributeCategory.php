<?php


namespace App\Entity;


use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;

class ListingAttributeCategory
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var AttributeInterface
     */
    protected $attribute;

    /**
     * @var TaxonInterface
     */
    protected $taxon;

    /**
     * @var int
     */
    protected $position;

    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }

    public function setAttribute(?AttributeInterface $attribute): void
    {
        $this->attribute = $attribute;
    }

    public function getCategory(): ?TaxonInterface
    {
        return $this->taxon;
    }

    public function setCategory(?TaxonInterface $taxon): void
    {
        $this->taxon = $taxon;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }
}