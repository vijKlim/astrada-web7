<?php


namespace App\Utils;

use App\Entity\LocalBusiness;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class ServiceEditor
{
    private $business;
    private $service;

    public function __construct(LocalBusiness $business, $service)
    {
        $this->business = $business;
        $this->service = $service;
    }

    public function getName()
    {
        return $this->service->getName();
    }

    public function setName($name)
    {
        return $this->service->setName($name);
    }

    public function getChildren()
    {
        return $this->service->getChildren();
    }

    public function getProducts()
    {
        $products = new ArrayCollection();
        foreach ($this->business->getProducts() as $product) {
            $products->add($product);
        }
        foreach ($this->service->getChildren() as $child) {
            foreach ($child->getProducts() as $product) {
                $products->removeElement($product);
            }
        }

        return $products;
    }

    public function setProducts(Collection $products)
    {
        $this->products = $products;
    }
}