<?php


namespace App\Entity;


use Astrada\SubscriptionBundle\Model\SubscriptionInterface;
use Astrada\SubscriptionBundle\Model\SubscriptionProductInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class ListingSubscription implements SubscriptionInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Listing
     */
    private $product;

    /**
     * @var \DateTimeImmutable
     */
    private $startDate;

    /**
     * @var \DateTimeImmutable
     */
    private $endDate;

    /**
     * @var boolean
     */
    private $active;

    /**
     * @var boolean
     */
    private $autoRenewal;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $strategy;


    public function __construct()
    {
        $this->setStartDate(new \DateTimeImmutable());
        $this->setEndDate(null);
        $this->setActive(false);
        $this->setAutoRenewal(false);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     *
     * @return ListingSubscription
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Listing
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param SubscriptionProductInterface $product
     *
     * @return ListingSubscription
     */
    public function setProduct(SubscriptionProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDate(\DateTimeImmutable $startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndDate($finishDate)
    {
        $this->endDate = $finishDate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function activate()
    {
        return $this->setActive(true);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate()
    {
        return $this->setActive(false);
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
     * @return ListingSubscription
     */
    public function setAutoRenewal($autoRenewal)
    {
        $this->autoRenewal = $autoRenewal;

        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     *
     * @return ListingSubscription
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return SubscriptionInterface
     */
    public function setStrategy($name)
    {
        $this->strategy = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return sprintf(
            '%s - %s',
            $this->getStartDate() ? $this->getStartDate()->format('Y-m-d H:i') : '??',
            $this->getEndDate() ? $this->getEndDate()->format('Y-m-d H:i') : '??'
        );
    }
}