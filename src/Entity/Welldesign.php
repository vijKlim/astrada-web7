<?php


namespace App\Entity;


use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class Welldesign implements ResourceInterface
{
    /**
     *
     * @var integer
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $pipeDiameter;

    /**
     *
     * @var integer
     */
    private $depthFrom;

    /**
     *
     * @var integer
     */
    private $depthTo;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    protected $vehicleType;

    /**
     * @var int
     */
    protected $price;

    /**
     * @var Listing
     *
     * @Groups({"listing", "listing_seo","listing_public"})
     */
    protected $listing;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPipeDiameter(): string
    {
        return $this->pipeDiameter;
    }

    /**
     * @param string $pipeDiameter
     */
    public function setPipeDiameter(string $pipeDiameter): void
    {
        $this->pipeDiameter = $pipeDiameter;
    }

    /**
     * @return int
     */
    public function getDepthFrom(): int
    {
        return $this->depthFrom;
    }

    /**
     * @param int $depthFrom
     */
    public function setDepthFrom(int $depthFrom): void
    {
        $this->depthFrom = $depthFrom;
    }

    /**
     * @return int
     */
    public function getDepthTo(): int
    {
        return $this->depthTo;
    }

    /**
     * @param int $depthTo
     */
    public function setDepthTo(int $depthTo): void
    {
        $this->depthTo = $depthTo;
    }

    /**
     * @return string
     */
    public function getVehicleType(): string
    {
        return $this->vehicleType;
    }

    /**
     * @param string $vehicleType
     */
    public function setVehicleType(string $vehicleType): void
    {
        $this->vehicleType = $vehicleType;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    /**
     * @return Listing
     */
    public function getListing(): Listing
    {
        return $this->listing;
    }

    /**
     * @param Listing $listing
     */
    public function setListing(Listing $listing): void
    {
        $this->listing = $listing;
    }
}