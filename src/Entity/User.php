<?php

declare(strict_types=1);

namespace App\Entity;

use App\Sylius\Customer\CustomerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\Timestampable;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Nucleos\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @phpstan-extends User<\Nucleos\UserBundle\Model\GroupInterface>
 */
class User extends BaseUser
{
    use Timestampable;

    protected $id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="3", max="15")
     * @Assert\Regex(pattern="/^[a-zA-Z0-9_]{3,15}$/")
     * @var string
     */
    protected $username;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    protected $email;


    protected $quotesAllowed = false;

    /**
     * @var CustomerInterface|null
     */
    protected $customer;

    private $businesses;

    /**
     * @var Booking[]
     */
    protected $bookings;

    public function __construct()
    {
        $this->businesses = new ArrayCollection();
        $this->bookings = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getGivenName()
    {
        if (null !== $this->customer) {
            return $this->customer->getFirstName();
        }
    }

    /**
     * @param mixed $givenName
     */
    public function setGivenName($givenName)
    {
        if (null !== $this->customer) {
            $this->customer->setFirstName($givenName);
        }
    }

    /**
     * @return mixed
     */
    public function getFamilyName()
    {
        if (null !== $this->customer) {
            return $this->customer->getLastName();
        }
    }

    /**
     * @param mixed $familyName
     */
    public function setFamilyName($familyName)
    {
        if (null !== $this->customer) {
            $this->customer->setLastName($familyName);
        }
    }

    /**
     * @return mixed
     */
    public function getTelephone()
    {
        if (null !== $this->customer) {

            $phoneNumber = $this->customer->getPhoneNumber();

            if (!empty($phoneNumber)) {
                try {
                    return PhoneNumberUtil::getInstance()->parse($phoneNumber);
                } catch (NumberParseException $e) {}
            }
        }
    }

    /**
     * @param PhoneNumber|string $telephone
     */
    public function setTelephone($telephone)
    {
        if (null !== $this->customer) {
            $this->customer->setTelephone($telephone);
        }
    }

    /**
     * @return mixed
     */
    public function isQuotesAllowed()
    {
        return $this->quotesAllowed || $this->hasRole('ROLE_ADMIN');
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

    public function setId(string $id): void
    {
        $this->id = $id;
    }



    /**
     * {@inheritdoc}
     */
    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomer(?CustomerInterface $customer): void
    {
        if ($this->customer === $customer) {
            return;
        }

        $previousCustomer = $this->customer;
        $this->customer = $customer;

        if ($previousCustomer instanceof CustomerInterface) {
            $previousCustomer->setUser(null);
        }

        if ($customer instanceof CustomerInterface) {
            $customer->setUser($this);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function setEmail(string $email): void
    {
        parent::setEmail($email);

        if (null !== $this->customer) {
            $this->customer->setEmail($email);
        }
    }


    public function setBusinesses($businesses)
    {
        $this->businesses = $businesses;

        return $this;
    }

    public function addBusiness(LocalBusiness $business)
    {
        if (!$this->businesses->contains($business)) {
            $this->businesses->add($business);
        }

        return $this;
    }

    public function ownsBusiness(LocalBusiness $business)
    {
        return $this->businesses->contains($business);
    }

    public function getBusinesses()
    {
        return $this->businesses;
    }


    /**
     * @return ArrayCollection|Booking[]
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * @param ArrayCollection|Booking[] $bookings
     */
    public function setBookings(ArrayCollection $bookings)
    {
        foreach ($bookings as $booking) {
            $booking->setUser($this);
        }

        $this->bookings = $bookings;
    }

    public function addAddress(Address $address)
    {
        $this->customer->addAddress($address);

        return $this;
    }

    public function removeAddress(Address $address)
    {
        $this->customer->removeAddress($address);

        return $this;
    }

    public function getAddresses()
    {
        return $this->customer->getAddresses();
    }
}
