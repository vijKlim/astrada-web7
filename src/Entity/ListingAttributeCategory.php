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
    protected $category;

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
        return $this->category;
    }

    public function setCategory(?TaxonInterface $category): void
    {
        $this->category = $category;
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