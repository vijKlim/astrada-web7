<?php


namespace App\Entity;

use App\Entity\Base\BaseBooking;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\Timestampable;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Review\Model\Review;
use Symfony\Component\Validator\Constraints as Assert;

class Booking extends BaseBooking implements ResourceInterface
{
    use Timestampable;
    use SoftDeleteableEntity;

    /**
     * @var integer
     */
    private $id;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @var User
     */
    protected $user;


    /**
     * @var Listing
     */
    protected $listing;


    /**
     * @var Topic
     */
    private $topic;




    public function __construct()
    {
        parent::__construct();
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
     * Set user
     *
     * @param User|null $user
     * @return Booking
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set listing
     *
     * @param Listing $listing
     * @return Booking
     */
    public function setListing(Listing $listing)
    {
        $this->listing = $listing;

        return $this;
    }

    /**
     * Get listing
     *
     * @return Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param Topic $topic
     */
    public function setTopic($topic)
    {
        $topic->setBooking($this);
        $this->topic = $topic;
    }

    /**
     * Compute the number of time units (days, hours, ...) from booking dates
     * If we are not in time unit day mode, the duration is equal to the number of days multiplied
     * by the number of time units.
     *
     * @param boolean $endDayIncluded
     * @param int     $timeUnit
     *
     * @return int|bool  nb time units
     */
    public function getDuration($endDayIncluded, $timeUnit)
    {
        if (!$this->getStart() || !$this->getEnd()) {
            return false;
        }

        $timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
        $durationDay = $this->getDateRange()->getDuration($endDayIncluded);

        if ($durationDay < 1) {
            return false;
        }

        if ($timeUnitIsDay) {//Duration in time unit (day)
            $duration = $durationDay;
        } else {//Duration in time unit (hour, ...)
            if (!$this->getStartTime() || !$this->getEndTime()) {
                return false;
            }
            $durationTime = $this->getTimeRange()->getDuration($timeUnit);
            $duration = $durationDay * $durationTime;
        }

        return $duration;
    }

    /**
     * Get time in seconds before booking request expire depending on its status
     *
     * @param int $expirationDelay  in minutes
     * @param int $acceptationDelay in minutes
     *
     * @return bool|int nb seconds before expiration
     */
    public function getTimeBeforeExpiration($expirationDelay, $acceptationDelay)
    {
        switch ($this->getStatus()) {
            case self::STATUS_DRAFT:
                return false;
                break;
            case self::STATUS_NEW:
                $expirationDate = $this->getExpirationDate($expirationDelay, $acceptationDelay);
                if ($expirationDate) {
                    $now = new DateTime('now');

                    return round($expirationDate->getTimestamp() - $now->getTimestamp());
                }

                return false;

                break;
            default:
                //No expiration case
                return false;
        }
    }

    /**
     * Get booking expiration date:
     *   Equal to the smallest date between (new booking date + expiration delay) and (booking start date + acceptation delay)
     *
     * @param int $expirationDelay  in minutes
     * @param int $acceptationDelay in minutes
     *
     * @return Datetime|bool (in UTC)
     */
    public function getExpirationDate($expirationDelay, $acceptationDelay)
    {
        if ($this->getNewBookingAt()) {
            $expirationDate = clone $this->getNewBookingAt();
            $expirationDate->add(new DateInterval('PT'.$expirationDelay.'M'));

            if ($expirationDelay >= 0) {
                $expirationDate->add(new DateInterval('PT' . $expirationDelay . 'M'));
            } else {
                $expirationDate->sub(new DateInterval('PT' . (-$expirationDelay) . 'M'));
            }

            $acceptationDate = clone $this->getStart();
            $acceptationDate->sub(new DateInterval('PT' . $acceptationDelay . 'M'));

            //Return minus date
            if ($expirationDate->format('Ymd H:i') < $acceptationDate->format('Ymd H:i')) {
                return $expirationDate;
            } else {
                return $acceptationDate;
            }
        }

        return false;
    }


    /**
     * Get booking validation date.
     * This is the date when the booking is considered as validated (started, or finished; ... ) according to the
     * cocorico.booking.validated_moment and cocorico.booking.validated_delay parameters.
     * At this moment the offerer can be payed.
     *
     * @param string $bookingValidationMoment ("start"|"end")
     * @param int    $bookingValidationDelay  in minutes
     *
     * @return Datetime|bool (in UTC)
     */
    public function getValidationDate($bookingValidationMoment, $bookingValidationDelay)
    {
        $methodName = "get" . ucfirst($bookingValidationMoment);
        /** @var DateTime $validatedAt */
        $validatedAt = $this->$methodName();
        if ($validatedAt) {
            $validatedAtCloned = clone $validatedAt;
            if ($bookingValidationDelay >= 0) {
                $validatedAtCloned->add(new DateInterval('PT'.$bookingValidationDelay.'M'));
            } else {
                $validatedAtCloned->sub(new DateInterval('PT'.abs($bookingValidationDelay).'M'));
            }

            return $validatedAtCloned;
        }

        return false;
    }


    /**
     * Get time in seconds before booking start
     *
     * @return bool|int nb seconds before start
     */
    public function getTimeBeforeStart()
    {
        if ($this->getStart()) {
            $now = new DateTime('now');

            return $this->getStart()->getTimestamp() - $now->getTimestamp();
        }

        return false;
    }

    /**
     * Return whether a booking has started or not
     *
     * @return bool
     */
    public function hasStarted()
    {
        $now = new DateTime();

        return ($this->getStart()->format('Ymd') <= $now->format('Ymd'));
    }


    /**
     * Check if booking begin during or after the minimum start date time according to $minStartTimeDelay
     * old: hasCorrectStartTime
     *
     * @param int $minStartTimeDelay in minutes
     * @return bool
     */
    public function beginDuringOrAfterMinStartDate($minStartTimeDelay)
    {
        $minStartTime = new DateTime();
        $minStartTime->add(new DateInterval('PT'.$minStartTimeDelay.'M'));

        return $this->getStart()->format('Ymd H:i') >= $minStartTime->format('Ymd H:i');
    }


    /**
     * Check if booking begin after the maximum acceptable date according to $acceptationDelay
     *
     * @param int $acceptationDelay in minutes
     * @return bool
     */
    public function beginAfterMaxAcceptableDate($acceptationDelay)
    {
        $maxAcceptableDate = new DateTime();
        $maxAcceptableDate->add(new DateInterval('PT'.$acceptationDelay.'M'));

        return $this->getStart()->format('Ymd') > $maxAcceptableDate->format('Ymd');
    }

}