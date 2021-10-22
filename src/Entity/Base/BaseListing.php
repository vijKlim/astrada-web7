<?php


namespace App\Entity\Base;

use App\Validator\Constraints as AstradaAssert;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

/**
 * Listing
 *
 * @AstradaAssert\Listing()
 *
 * @ORM\MappedSuperclass
 */
abstract class BaseListing
{
    const STATUS_NEW = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_INVALIDATED = 3;
    const STATUS_SUSPENDED = 4;
    const STATUS_DELETED = 5;
    const STATUS_TO_VALIDATE = 6;

    public static $statusValues = array(
        self::STATUS_NEW => 'entity.listing.status.new',
        self::STATUS_PUBLISHED => 'entity.listing.status.published',
        self::STATUS_INVALIDATED => 'entity.listing.status.invalidated',
        self::STATUS_SUSPENDED => 'entity.listing.status.suspended',
        self::STATUS_DELETED => 'entity.listing.status.deleted',
        self::STATUS_TO_VALIDATE => 'entity.listing.status.to_validate'
    );

    public static $visibleStatus = array(
        self::STATUS_NEW,
        self::STATUS_PUBLISHED,
        self::STATUS_INVALIDATED,
        self::STATUS_SUSPENDED,
        self::STATUS_TO_VALIDATE
    );

    /**
     * @var integer
     */
    protected $status = self::STATUS_NEW;

    /**
     * @var boolean
     */
    protected $certified;

    /**
     * @var string The telephone number.
     * @Groups({"order", "business"})
     * @AssertPhoneNumber
     */
    protected $telephone;

    /**
     * @var integer
     */
    protected $minDuration;

    /**
     * @var integer
     */
    protected $maxDuration;

    /**
     * @var integer
     */
    protected $averageRating;

    /**
     * @var integer
     */
    protected $commentCount = 0;

    /**
     * Admin notation
     * @var float
     */
    protected $adminNotation;

    /**
     * @var \DateTime
     */
    protected $availabilitiesUpdatedAt;

    /**
     * Set status
     *
     * @param  integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for listing.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
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
     * Return available status for current status
     *
     * @param int $status
     *
     * @return array
     */
    public static function getAvailableStatusValues($status)
    {
        $availableStatus = array(self::STATUS_DELETED);

        if ($status == self::STATUS_NEW) {
            $availableStatus[] = self::STATUS_PUBLISHED;
        } elseif ($status == self::STATUS_PUBLISHED) {
            $availableStatus[] = self::STATUS_SUSPENDED;
        } elseif ($status == self::STATUS_INVALIDATED) {
            $availableStatus[] = self::STATUS_TO_VALIDATE;
        } elseif ($status == self::STATUS_SUSPENDED) {
            $availableStatus[] = self::STATUS_PUBLISHED;
        }

        //Prepend current status to visible status
        array_unshift($availableStatus, $status);

        //Construct associative array with keys equals to status values and values to label of status
        $status = array_intersect_key(
            self::$statusValues,
            array_flip($availableStatus)
        );

        return $status;
    }


    /**
     * @return boolean
     */
    public function isCertified()
    {
        return $this->certified;
    }

    /**
     * @param boolean $certified
     */
    public function setCertified($certified)
    {
        $this->certified = $certified;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinDuration()
    {
        return $this->minDuration;
    }

    /**
     * @param int $minDuration
     */
    public function setMinDuration($minDuration)
    {
        $this->minDuration = $minDuration;
    }

    /**
     * @return int
     */
    public function getMaxDuration()
    {
        return $this->maxDuration;
    }

    /**
     * @param int $maxDuration
     */
    public function setMaxDuration($maxDuration)
    {
        $this->maxDuration = $maxDuration;
    }


    /**
     * Set commentCount
     *
     * @param  integer $commentCount
     * @return $this
     */
    public function setCommentCount($commentCount)
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    /**
     * Get commentCount
     *1
     *
     * @return integer
     */
    public function getCommentCount()
    {
        return $this->commentCount;
    }

    /**
     * @return float
     */
    public function getAdminNotation()
    {
        return $this->adminNotation;
    }

    /**
     * @param float $adminNotation
     */
    public function setAdminNotation($adminNotation)
    {
        $this->adminNotation = $adminNotation;
    }

    /**
     * @return \DateTime
     */
    public function getAvailabilitiesUpdatedAt()
    {
        return $this->availabilitiesUpdatedAt;
    }

    /**
     * @param \DateTime $availabilitiesUpdatedAt
     */
    public function setAvailabilitiesUpdatedAt($availabilitiesUpdatedAt)
    {
        $this->availabilitiesUpdatedAt = $availabilitiesUpdatedAt;
    }
}