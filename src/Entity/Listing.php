<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Base\BaseListing;
use App\Entity\LocalBusiness\ImageTrait;
use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use App\Sylius\Product\ProductInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\Timestampable;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Component\Review\Model\ReviewableInterface;
use Sylius\Component\Review\Model\ReviewInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ApiResource(
 *   shortName="Listing",
 *   attributes={
 *     "denormalization_context"={"groups"={ "listing_update"}},
 *     "normalization_context"={"groups"={"listing", "address"}}
 *   },
 *   collectionOperations={
 *     "get"={
 *       "method"="GET",
 *       "pagination_enabled"=false,
 *       "normalization_context"={"groups"={"listing", "address"}}
 *     },
 *   },
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "normalization_context"={"groups"={"listing", "address"}},
 *       "security"="is_granted('view', object)"
 *     },
 *   }
 * )
 * @Vich\Uploadable
 */
class Listing extends BaseListing implements ResourceInterface, TranslatableInterface, ReviewableInterface, SubscriptionProductInterface
{
    use Timestampable;
    use SoftDeleteableEntity;
    use ImageTrait;

    use TranslatableTrait {
        __construct as private initializeTranslationsCollection;
    }

    /**
     *
     * @var integer
     */
    private $id;

    /**
     * @var Address
     *
     * @Groups({"listing", "listing_seo","listing_public"})
     */
    protected $address;

    /**
     * @var LocalBusiness
     *
     * @Groups({"listing", "listing_seo","listing_public"})
     */
    protected $business;

    protected $taxons;

    protected $welldesign;

    /**
     * @var ArrayCollection
     */
    protected $reviews;

    /**
     * @var ArrayCollection
     */
    protected $bookings;

    /**
     * @var float
     */
    protected $averageRating = 0;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    protected $viewCount = 0;

    /**
     * @var SubscriptionProductInterface
     */
    private $nextRenewalProduct;

    /**
     * @var integer
     *
     * Duration in seconds.
     */
    private $duration;

    /**
     * @var integer|null
     */
    private $quota;

    /**
     * @var boolean
     */
    private $autoRenewal;

    /**
     * @var boolean
     */
    private $default;

    /**
     * @var \DateTime
     */
    private $expirationDate;

    /**
     * @var string
     */
    private $strategyCodeName;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
        $this->taxons = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->bookings = new ArrayCollection();

        $this->setWelldesign(new Welldesign());
        $this->setDefault(false);
        $this->setExpirationDate(null);
        $this->setAutoRenewal(false);
        $this->setStrategyCodeName('end_last');
    }

    protected function createTranslation()
    {
        return new ListingTranslation();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): ?string
    {
        return $this->getTitle();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getTranslation()->getTitle();
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->getTranslation()->setTitle($title);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getTranslation()->getDescription();
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->getTranslation()->setDescription($description);
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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

    public function getTaxons()
    {
        return $this->taxons;
    }

    public function hasTaxons(TaxonInterface $taxon)
    {
        return $this->taxons->contains($taxon);
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


    public function getWelldesign()
    {
        return $this->welldesign;
    }



    public function setWelldesign(ResourceInterface $welldesign)
    {
        $welldesign->setListing($this);
        $this->welldesign = $welldesign;
    }


    /**
     * @return ListingReview[]|Collection
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    /**
     * @param ReviewInterface $review
     *
     * @return bool
     */
    public function hasReview(ReviewInterface $review): bool
    {
        return $this->reviews->contains($review);
    }

    /**
     * @param ReviewInterface $review
     */
    public function addReview(ReviewInterface $review): void
    {
        if (!$this->hasReview($review)) {
            $review->setReviewSubject($this);
            $this->reviews->add($review);
        }
    }

    /**
     * @param ReviewInterface $review
     */
    public function removeReview(ReviewInterface $review): void
    {
        $this->reviews->removeElement($review);
    }

    /**
     * {@inheritdoc}
     */
    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    /**
     * {@inheritdoc}
     */
    public function setAverageRating(float $averageRating): void
    {
        $this->averageRating = $averageRating;
    }

    /**
     * @return mixed
     */
    public function getNextRenewalProduct()
    {
        return $this->nextRenewalProduct;
    }

    /**
     * @param mixed $nextRenewalProduct
     *
     * @return Listing
     */
    public function setNextRenewalProduct($nextRenewalProduct)
    {
        $this->nextRenewalProduct = $nextRenewalProduct;

        return $this;
    }

    /**
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param integer $duration
     *
     * @return Listing
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getQuota()
    {
        return $this->quota;
    }

    /**
     * @param int|null $quota
     *
     * @return Listing
     */
    public function setQuota($quota)
    {
        $this->quota = $quota;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoRenewal()
    {
        return $this->autoRenewal;
    }

    /**
     * @param bool $autoRenewal
     *
     * @return Listing
     */
    public function setAutoRenewal($autoRenewal)
    {
        $this->autoRenewal = $autoRenewal;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     *
     * @return Listing
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param \DateTime $expirationDate
     *
     * @return Listing
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getStrategyCodeName()
    {
        return $this->strategyCodeName;
    }

    /**
     * @param string $strategyCodeName
     *
     * @return Listing
     */
    public function setStrategyCodeName($strategyCodeName)
    {
        $this->strategyCodeName = $strategyCodeName;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getTitle();
    }

    public static function toExpressionLanguageValues(Listing $listing)
    {
        return [
        ];
    }
}