<?php


namespace App\Entity\LocalBusiness;

use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Base\BaseListing;
use App\Entity\Listing\ListingPricingRuleSet;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Symfony\Component\Serializer\Annotation\Groups;

trait CatalogTrait
{
    //TODO add @ApiSubresource
    protected $products;

    //TODO add @ApiSubresource
    protected $productOptions;

    protected $taxons;

    protected $listings;

    protected $listingPricingRuleSets;

    /**
     * @Groups({"business"})
     */
    protected $activeServiceTaxon;

    /* Listings */

    public function getListings()
    {
        return $this->listings;
    }

    public function hasListing(BaseListing $listing)
    {
        return $this->listings->contains($listing);
    }

    public function addListing(BaseListing $listing)
    {
        if (!$this->hasListing($listing)) {
            $listing->setBusiness($this);
            $this->listings->add($listing);
        }
    }

    public function removeListing(BaseListing $listing)
    {
        if ($this->hasListing($listing)) {
            $this->listings->removeElement($listing);
            $listing->setBusiness(null);
        }
    }

    /* listingPricingRuleSets */

    public function getListingPricingRuleSets()
    {
        return $this->listingPricingRuleSets;
    }

    public function hasListingPricingRuleSet(ListingPricingRuleSet $listingPricingRuleSet)
    {
        return $this->listingPricingRuleSets->contains($listingPricingRuleSet);
    }

    public function addListingPricingRuleSet(ListingPricingRuleSet $listingPricingRuleSet)
    {
        if (!$this->hasListingPricingRuleSet($listingPricingRuleSet)) {
            $listingPricingRuleSet->setBusiness($this);
            $this->listingPricingRuleSets->add($listingPricingRuleSet);
        }
    }

    public function removeListingPricingRuleSet(ListingPricingRuleSet $listingPricingRuleSet)
    {
        if ($this->hasListingPricingRuleSet($listingPricingRuleSet)) {
            $this->listingPricingRuleSets->removeElement($listingPricingRuleSet);
            $listingPricingRuleSet->setBusiness(null);
        }
    }

    /* Products */

    public function getProducts()
    {
        return $this->products;
    }

    public function hasProduct(ProductInterface $product)
    {
        return $this->products->contains($product);
    }

    public function addProduct(ProductInterface $product)
    {
        if (!$this->hasProduct($product)) {
            $product->setBusiness($this);
            $this->products->add($product);
        }
    }

    public function removeProduct(ProductInterface $product)
    {
        if ($this->hasProduct($product)) {
            $this->products->removeElement($product);
            $product->setBusiness(null);
        }
    }

    /* Options */

    public function getProductOptions()
    {
        return $this->productOptions;
    }

    public function addProductOption(ProductOptionInterface $productOption)
    {
        if (!$this->productOptions->contains($productOption)) {
            $productOption->setBusiness($this);
            $this->productOptions->add($productOption);
        }
    }

    public function removeProductOption(ProductOptionInterface $productOption)
    {
        if ($this->productOptions->contains($productOption)) {
            $this->productOptions->removeElement($productOption);
            $productOption->setBusiness(null);
        }
    }

    /* Menus / Taxons */

    public function getActiveServiceTaxon()
    {
        return $this->activeServiceTaxon;
    }

    public function getServiceTaxon()
    {
        return $this->activeServiceTaxon;
    }

    public function setServiceTaxon(TaxonInterface $taxon)
    {
        $this->activeServiceTaxon = $taxon;
    }

    public function hasService()
    {
        return null !== $this->activeServiceTaxon;
    }

    public function getTaxons()
    {
        return $this->taxons;
    }

    public function addTaxon(TaxonInterface $taxon)
    {
        // TODO Check if this is a root taxon
        $this->taxons->add($taxon);
    }

    public function removeTaxon(TaxonInterface $taxon)
    {
        if ($this->getTaxons()->contains($taxon)) {
            $this->getTaxons()->removeElement($taxon);
        }
    }
}