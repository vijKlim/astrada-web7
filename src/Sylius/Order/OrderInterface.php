<?php


namespace App\Sylius\Order;

use App\DataType\TsRange;
use App\Entity\Address;
use App\Entity\Delivery;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusiness\FulfillmentMethod;
use App\Entity\Sylius\OrderEvent;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelAwareInterface;
use Sylius\Component\Customer\Model\CustomerAwareInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Sylius\Component\Payment\Model\PaymentsSubjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface OrderInterface
    extends
    BaseOrderInterface,
    PaymentsSubjectInterface,
    ChannelAwareInterface,
    CustomerAwareInterface
{
    public const STATE_ACCEPTED = 'accepted';
    public const STATE_REFUSED = 'refused';

    /**
     * @var string
     * The order was cancelled because customer contacted us to cancel.
     */
    public const CANCEL_REASON_CUSTOMER = 'CUSTOMER';

    /**
     * @var string
     * The order was cancelled because the establishment is sold out.
     */
    public const CANCEL_REASON_SOLD_OUT = 'SOLD_OUT';

    /**
     * @var string
     * The order was cancelled because the establishment is in "rush" mode and can't cope.
     */
    public const CANCEL_REASON_RUSH_HOUR = 'RUSH_HOUR';

    /**
     * @var string
     * The order was cancelled because the customer didn't show up.
     */
    public const CANCEL_REASON_NO_SHOW = 'NO_SHOW';

    /**
     * @return LocalBusiness|null
     */
    public function getBusiness(): ?LocalBusiness;

    /**
     * @return boolean
     */
    public function hasBusiness(): bool;

    /**
     * @return Address|null
     */
    public function getShippingAddress(): ?Address;

    /**
     * @return Address|null
     */
    public function getBillingAddress(): ?Address;

    /**
     * @return \DateTime|null
     */
    public function getShippedAt(): ?\DateTime;

    /**
     * @return TsRange|null
     */
    public function getShippingTimeRange(): ?TsRange;

    /**
     * @param string|null $state
     *
     * @return PaymentInterface|null
     */
    public function getLastPayment(?string $state = null): ?PaymentInterface;

    /**
     * @return Delivery|null
     */
    public function getDelivery(): ?Delivery;

    /**
     * @param Delivery $delivery
     */
    public function setDelivery(Delivery $delivery): void;

    /**
     * @return Collection|OrderEvent[]
     */
    public function getEvents(): Collection;

    /**
     * @return boolean
     */
    public function containsDisabledProduct(): bool;

    /**
     * @return boolean
     */
    public function isTakeaway(): bool;

    /**
     * @return string
     */
    public function getFulfillmentMethod(): string;


    /*
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;


    /**
     * @return int
     */
    public function getTransferAmount(LocalBusiness $subVendor): int;

    /**
     * @return \SplObjectStorage
     */
    public function getItemsGroupedByVendor(): \SplObjectStorage;


    /**
     * @param LocalBusiness $business
     * @return float
     */
    public function getPercentageForBusiness(LocalBusiness $business): float;

    /**
     * @return Address|null
     */
    public function getPickupAddress(): ?Address;

    public function getFulfillmentMethodObject(): ?FulfillmentMethod;


}