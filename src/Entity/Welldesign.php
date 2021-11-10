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
    protected $drillingKit;

    /**
     * @Assert\Type(type="string")
     */
    protected $transportationCost;

    /**
     * @Assert\Type(type="string")
     */
    protected $wellCost;


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
    public function getDrillingKit(): string
    {
        return $this->drillingKit;
    }

    /**
     * @param string $drillingKit
     */
    public function setDrillingKit(string $drillingKit): void
    {
        $this->drillingKit = $drillingKit;
    }

    /**
     * @return mixed
     */
    public function getTransportationCost()
    {
        return $this->transportationCost;
    }

    /**
     * @param mixed $transportationCost
     */
    public function setTransportationCost($transportationCost): void
    {
        $this->transportationCost = $transportationCost;
    }

    /**
     * @return mixed
     */
    public function getWellCost()
    {
        return $this->wellCost;
    }

    /**
     * @param mixed $wellCost
     */
    public function setWellCost($wellCost): void
    {
        $this->wellCost = $wellCost;
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