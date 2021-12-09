<?php


namespace App\Entity\Base;


use App\Entity\Booking;
use App\Entity\Model\DateRange;
use App\Entity\Model\DateTimeRange;
use App\Entity\Model\TimeRange;
use DateTime;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

abstract class BaseBooking
{
    /* Status */
    const STATUS_DRAFT = 0;
    const STATUS_NEW = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_EXPIRED = 4;
    const STATUS_CANCELED_ASKER = 6;
    const STATUS_DONE = 7;

    public static $statusValues = array(
        self::STATUS_DRAFT => 'entity.booking.status.draft',
        self::STATUS_NEW => 'entity.booking.status.new',
        self::STATUS_ACCEPTED => 'entity.booking.status.accepted',
        self::STATUS_EXPIRED => 'entity.booking.status.expired',
        self::STATUS_CANCELED_ASKER => 'entity.booking.status.canceled_asker',
        self::STATUS_DONE => 'entity.booking.status.done',
    );

    public static $visibleStatus = array(
        self::STATUS_NEW,
        self::STATUS_ACCEPTED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELED_ASKER,
        self::STATUS_DONE
    );



    //Status for which booking can be created
    public static $newableStatus = array(
        self::STATUS_DRAFT
    );

    //Status for which booking can be canceled by asker
    public static $cancelableStatus = array(
        self::STATUS_NEW,
    );

    //Status for which booking can be expired
    public static $expirableStatus = array(
        self::STATUS_DRAFT,
        self::STATUS_NEW
    );

    //Status for which booking can be payed
    public static $payableStatus = array(
        self::STATUS_NEW
    );



    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @var DateTime
     */
    protected $start;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @var DateTime
     */
    protected $end;

    /**
     * @var DateTime
     */
    protected $startTime;

    /**
     * @var DateTime
     */
    protected $endTime;

    /**
     * @var integer
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * @var boolean
     */
    protected $validated = false;

    /**
     * @var DateTime
     */
    protected $newBookingAt;

    /**
     * @var DateTime
     */
    protected $acceptedBookingAt;


    /**
     * @var DateTime
     */
    protected $canceledAskerBookingAt;

    /**
     * @var boolean
     */
    protected $alertedExpiring = false;


    /**
     * Initial booking message
     * @var string
     */
    protected $message;

    /**
     * @Assert\Type(type="string")
     */
    protected $priceTransportation;

    /**
     * @Assert\Type(type="string")
     */
    protected $priceWellDrilling;

    /**
     * @var integer
     */
    protected $distance;


    public function __construct()
    {

    }

    /**
     * Return visible status values
     *
     * @return array
     */
    public static function getVisibleStatusValues()
    {
        $status = array_intersect_key(self::$statusValues, array_flip(self::$visibleStatus));

        return $status;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param DateTime $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param DateTime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * Return date range according to booking start and end date
     *
     * @return DateRange
     */
    public function getDateRange()
    {
        return new DateRange($this->getStart(), $this->getEnd());
    }


    /**
     * @param DateRange $dateRange
     * @return Booking|BaseBooking
     */
    public function setDateRange(DateRange $dateRange)
    {
        $this->setStart($dateRange->getStart());
        $this->setEnd($dateRange->getEnd());

        return $this;
    }

    /**
     * Return time range according to booking start time and end time
     *
     * @return TimeRange
     */
    public function getTimeRange()
    {
        return new TimeRange($this->getStartTime(), $this->getEndTime(), $this->getStart());
    }

    /**
     * @param TimeRange $timeRange
     * @return $this
     */
    public function setTimeRange(TimeRange $timeRange)
    {
        $this->setStartTime($timeRange->getStart());
        $this->setEndTime($timeRange->getEnd());

        return $this;
    }

    /**
     * @return DateTimeRange
     */
    public function getDateTimeRange()
    {
        return new DateTimeRange($this->getDateRange(), array($this->getTimeRange()));
    }

    /**
     * Set status
     *
     * @param  integer $status
     * @return BaseBooking
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new InvalidArgumentException(
                sprintf('Invalid value for booking.status : %s.', $status)
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
     * @return DateTime
     */
    public function getNewBookingAt()
    {
        return $this->newBookingAt;
    }

    /**
     * @param DateTime $newBookingAt
     */
    public function setNewBookingAt($newBookingAt)
    {
        $this->newBookingAt = $newBookingAt;
    }


    /**
     * @return DateTime
     */
    public function getCanceledAskerBookingAt()
    {
        return $this->canceledAskerBookingAt;
    }

    /**
     * @param DateTime $canceledAskerBookingAt
     */
    public function setCanceledAskerBookingAt($canceledAskerBookingAt)
    {
        $this->canceledAskerBookingAt = $canceledAskerBookingAt;
    }

    /**
     * @return boolean
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * @param boolean $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
    }

    /**
     * @return boolean
     */
    public function isAlertedExpiring()
    {
        return $this->alertedExpiring;
    }

    /**
     * @param boolean $alertedExpiring
     */
    public function setAlertedExpiring($alertedExpiring)
    {
        $this->alertedExpiring = $alertedExpiring;
    }


    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getPriceTransportation()
    {
        return $this->priceTransportation;
    }

    /**
     * @param mixed $priceTransportation
     */
    public function setPriceTransportation($priceTransportation): void
    {
        $this->priceTransportation = $priceTransportation;
    }

    /**
     * @return mixed
     */
    public function getPriceWellDrilling()
    {
        return $this->priceWellDrilling;
    }

    /**
     * @param mixed $priceWellDrilling
     */
    public function setPriceWellDrilling($priceWellDrilling): void
    {
        $this->priceWellDrilling = $priceWellDrilling;
    }

    /**
     * @return int
     */
    public function getDistance(): int
    {
        return $this->distance;
    }

    /**
     * @param int $distance
     */
    public function setDistance(int $distance): void
    {
        $this->distance = $distance;
    }



    public function log($prefix = '')
    {
        echo "<br>Booking";
        if ($prefix) {
            echo "<br>$prefix";
        }

        echo '<br>Date: ';
        if ($this->getStart() && $this->getEnd()) {
            echo $this->getStart()->format('Y-m-d H:i').' / '.$this->getEnd()->format('Y-m-d H:i').'<br>';
        }


        echo 'Time: ';
        if ($this->getStartTime() && $this->getEndTime()) {
            echo $this->getStartTime()->format('Y-m-d H:i').' / '.$this->getEndTime()->format('Y-m-d H:i').'<br>';
        }
    }

}