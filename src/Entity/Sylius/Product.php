<?php


namespace App\Entity\Sylius;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\LocalBusiness;
use App\Sylius\Product\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Comparable;
use Sylius\Component\Product\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductOptionValueInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *   collectionOperations={
 *   },
 *   itemOperations={
 *     "get"={"method"="GET"},
 *     "put"={
 *       "method"="PUT",
 *       "denormalization_context"={"groups"={"product_update"}},
 *       "access_control"="is_granted('edit', object)"
 *     },
 *     "delete"={
 *       "method"="DELETE",
 *       "access_control"="is_granted('edit', object)"
 *     }
 *   },
 *   attributes={
 *     "normalization_context"={"groups"={"product"}}
 *   }
 * )
 */
class Product extends BaseProduct implements ProductInterface, Comparable
{
    protected $type;

    protected $deletedAt;

    protected $images;

    protected $business;

    public function __construct()
    {
        parent::__construct();

        $this->images = new ArrayCollection();
        $this->type = \App\Enum\Product::COMMON;
    }

    public function hasNonAdditionalOptions()
    {
        foreach ($this->getOptions() as $option) {
            if (!$option->isAdditional()) {
                return true;
            }
        }

        return false;
    }

    public function hasOptionValue(ProductOptionValueInterface $optionValue): bool
    {
        return $this->hasOption($optionValue->getOption());
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): Collection
    {
        $options = $this->options->toArray();

        uasort($options, function ($a, $b) {
            if ($a->getPosition() === $b->getPosition()) return 0;
            return $a->getPosition() < $b->getPosition() ? -1 : 1;
        });

        $values = array_map(
            function (ProductOptions $options) {
                return $options->getOption();
            },
            $options
        );

        return new ArrayCollection($values);
    }

    /**
     * {@inheritdoc}
     */
    public function addOption(ProductOptionInterface $option): void
    {
        if (!$this->hasOption($option)) {

            $productOptions = new ProductOptions();
            $productOptions->setProduct($this);
            $productOptions->setOption($option);

            $this->options->add($productOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeOption(ProductOptionInterface $option): void
    {
        if ($this->hasOption($option)) {
            foreach ($this->options as $productOptions) {
                if ($productOptions->getOption() === $option) {
                    $this->options->removeElement($productOptions);
                    break;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption(ProductOptionInterface $option): bool
    {
        return $this->getOptions()->contains($option);
    }

    public function getPositionForOption(ProductOptionInterface $option): int
    {
        if ($this->hasOption($option)) {
            foreach ($this->options as $productOptions) {
                if ($productOptions->getOption() === $option) {
                    return $productOptions->getPosition();
                }
            }
        }

        return -1;
    }

    public function addOptionAt(ProductOptionInterface $option, int $position): void
    {
        if (!$this->hasOption($option)) {
            $productOptions = new ProductOptions();
            $productOptions->setProduct($this);
            $productOptions->setOption($option);
            $productOptions->setPosition($position);

            $this->options->add($productOptions);
        } else {
            foreach ($this->options as $productOptions) {
                if ($productOptions->getOption() === $option) {
                    $productOptions->setPosition($position);
                    break;
                }
            }
        }
    }

    public function getProductOptions()
    {
        return $this->options;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function addImage(ProductImage $image)
    {
        $image->setProduct($this);

        $this->images->add($image);
    }

    /**
     * Fix "Nesting level too deep - recursive dependency?"
     * @see https://github.com/Atlantic18/DoctrineExtensions/pull/2185
     */
    public function compareTo($other)
    {
        return $this === $other;
    }

    /**
     * @return LocalBusiness|null
     */
    public function getBusiness(): ?LocalBusiness
    {
        return $this->business;
    }

    /**
     * @param LocalBusiness|null $restaurant
     */
    public function setBusiness(?LocalBusiness $business)
    {
        $this->business = $business;
    }


    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}