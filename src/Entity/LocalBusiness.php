<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Api\Dto\BusinessInput;
use App\Entity\Base\LocalBusiness as BaseLocalBusiness;
use App\Entity\LocalBusiness\CatalogInterface;
use App\Entity\LocalBusiness\CatalogTrait;
use App\Entity\LocalBusiness\ClosingRulesTrait;
use App\Entity\LocalBusiness\FulfillmentMethodsTrait;
use App\Entity\LocalBusiness\ImageTrait;
use App\Entity\LocalBusiness\TransportationOptionsTrait;
use App\Enum\HomeAndConstructionBusiness;
use App\Enum\Store;
use App\OpeningHours\OpenCloseTrait;
use App\Validator\Constraints\IsActivableBusiness as AssertIsActivableBusiness;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ApiResource(
 *   shortName="Business",
 *   attributes={
 *     "denormalization_context"={"groups"={ "business_update"}},
 *     "normalization_context"={"groups"={"business", "address"}}
 *   },
 *   collectionOperations={
 *     "get"={
 *       "method"="GET",
 *       "pagination_enabled"=false,
 *       "normalization_context"={"groups"={"business", "address"}}
 *     },
 *     "me_businesses"={
 *       "method"="GET",
 *       "path"="/me/businesses",
 *       "controller"=MyBusinesses::class
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "normalization_context"={"groups"={"business", "address", "business_potential_action"}},
 *       "security"="is_granted('view', object)"
 *     },
 *     "put"={
 *       "method"="PUT",
 *       "input"=BusinessInput::class,
 *       "denormalization_context"={"groups"={"business_update"}},
 *       "security"="is_granted('edit', object)"
 *     },
 *     "close"={
 *       "method"="PUT",
 *       "path"="/businesses/{id}/close",
 *       "controller"=CloseController::class,
 *       "security"="is_granted('edit', object)"
 *     },
 *     "business_deliveries"={
 *       "method"="GET",
 *       "path"="/businesses/{id}/deliveries/{date}",
 *       "controller"=BusinessDeliveriesController::class,
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "normalization_context"={"groups"={"delivery", "address", "business_delivery"}}
 *     },
 *     "business_timing"={
 *       "method"="GET",
 *       "path"="/businesses/{id}/timing",
 *       "controller"=Timing::class,
 *       "normalization_context"={"groups"={"business_timing"}}
 *     }
 *   }
 * )
 * @Vich\Uploadable
 * @AssertIsActivableBusiness(groups="activable")
 */
class LocalBusiness extends BaseLocalBusiness implements
    CatalogInterface
{
    use Timestampable;
    use SoftDeleteableEntity;
    use OpenCloseTrait;
    use ClosingRulesTrait;
    use FulfillmentMethodsTrait;
    use TransportationOptionsTrait;
    use CatalogTrait;
    use ImageTrait;

    /**
     * @var int
     * @Groups({"business","listing","listing_public"})
     */
    protected $id;

    protected $type = HomeAndConstructionBusiness::GENERAL_CONTRACTOR;

    const STATE_NORMAL = 'normal';
    const STATE_RUSH = 'rush';
    const STATE_PLEDGE = 'pledge';

    /**
     * @var string The name of the item
     *
     * @Assert\Type(type="string")
     * @ApiProperty(iri="http://schema.org/name")
     * @Groups({"business", "business_seo","listing_public"})
     */
    protected $name;

    /**
     * @Groups({"business", "business_seo","listing","listing_public"})
     */
    protected $description;

    /**
     * @var boolean Is the business enabled?
     *
     * A disable business is not shown to visitors, but remain accessible in preview to admins and owners.
     *
     * @Groups({"business"})
     */
    protected $enabled = false;

    protected $quotesAllowed = false;

    /**
     * @var Address
     *
     * @Groups({"business", "business_seo"})
     */
    protected $address;

    /**
     * @var Address|null
     */
    protected $businessAddress;

    /**
     * @var string The website of the restaurant.
     *
     * @ApiProperty(iri="https://schema.org/URL")
     */
    protected $website;

    protected $owners;

    /**
     * @Groups({"business", "business_update"})
     */
    protected $state = self::STATE_NORMAL;

    protected $featured = false;
    protected $hub;



    public function __construct()
    {
        $this->closingRules = new ArrayCollection();
        $this->owners = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->listings = new ArrayCollection();
        $this->listingPricingRuleSets = new ArrayCollection();
        $this->productOptions = new ArrayCollection();
        $this->taxons = new ArrayCollection();
        $this->fulfillmentMethods = new ArrayCollection();
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

    /**
     * Sets name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getOwners(): Collection
    {
        return $this->owners;
    }

    public function addOwner(User $owner)
    {
        $owner->addBusiness($this);

        $this->owners->add($owner);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function setFeatured(bool $featured)
    {
        $this->featured = $featured;
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getBusinessAddress($fallback = false)
    {
        if ($fallback) {
            return $this->businessAddress ?? $this->address;
        }

        return $this->businessAddress;
    }

    public function setBusinessAddress(?Address $address)
    {
        $this->businessAddress = $address;
    }

    public function hasDifferentBusinessAddress()
    {
        return $this->businessAddress !== null;
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isQuotesAllowed()
    {
        return $this->quotesAllowed;
    }

    /**
     * @param mixed $quotesAllowed
     *
     * @return self
     */
    public function setQuotesAllowed($quotesAllowed)
    {
        $this->quotesAllowed = $quotesAllowed;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getContext()
    {
        if ($found = Store::search($this->type)) {
            return Store::class;
        }

        return HomeAndConstructionBusiness::class;
    }
}