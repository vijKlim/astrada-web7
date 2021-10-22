<?php


namespace App\Entity;


use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Sylius\Component\Resource\Model\ResourceInterface;

class ListingAvailability implements ResourceInterface
{
    const STATUS_AVAILABLE = 1;
    const STATUS_UNAVAILABLE = 2;
    const STATUS_BOOKED = 3;

    public static $statusValues = array(
        self::STATUS_AVAILABLE => 'entity.listing_availability.status.available',
        self::STATUS_UNAVAILABLE => 'entity.listing_availability.status.unavailable',
        self::STATUS_BOOKED => 'entity.listing_availability.status.booked'
    );

    public static $visibleValues = array(
        self::STATUS_AVAILABLE => 'entity.listing_availability.status.available',
        self::STATUS_UNAVAILABLE => 'entity.listing_availability.status.unavailable'
    );


    protected $id;

    protected $listingId;

    protected $day;

    protected $status;


    /**
     * Get id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set listingId
     *
     * @param int $listingId
     * @return self
     */
    public function setListingId($listingId)
    {
        $this->listingId = intval($listingId);

        return $this;
    }

    /**
     * Get listingId
     *
     * @return int $listingId
     */
    public function getListingId()
    {
        return $this->listingId;
    }

    /**
     * Set day
     *
     * @param DateTime $day
     * @return self
     */
    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return DateTime $day
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new InvalidArgumentException(
                sprintf('Invalid value for availability.status : %s.', $status)
            );
        }
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusValues[$this->getStatus()];
    }

    /**
     * Get Status Keys
     */
    public static function getStatusValuesKeys()
    {
        return array_keys(self::$statusValues);
    }

}