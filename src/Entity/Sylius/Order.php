<?php


namespace App\Entity\Sylius;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\DataType\TsRange;
use App\Entity\Address;
use App\Entity\Delivery;
use App\Entity\LocalBusiness;
use App\Entity\LocalBusiness\FulfillmentMethod;
use App\Entity\Vendor;
use App\Filter\OrderDateFilter;
use App\Sylius\Order\OrderInterface;
use App\Sylius\Order\OrderItemInterface;
use App\Validator\Constraints\ShippingAddress as AssertShippingAddress;
use App\Validator\Constraints\ShippingTimeRange as AssertShippingTimeRange;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\Order as BaseOrder;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


class Order extends BaseOrder implements OrderInterface
{
    protected $customer;

    protected $business;

    /**
     * @Assert\Valid
     * @AssertShippingAddress
     */
    protected $shippingAddress;

    protected $billingAddress;

    protected $payments;

    protected $delivery;

    protected $events;


    protected $channel;




    /**
     * @Assert\Expression(
     *   "!this.isTakeaway() or (this.isTakeaway() and this.getRestaurant().isFulfillmentMethodEnabled('collection'))",
     *   message="order.collection.not_available",
     *   groups={"cart"}
     * )
     */
    protected $takeaway = false;


    const SWAGGER_CONTEXT_TIMING_RESPONSE_SCHEMA = [
        "type" => "object",
        "properties" => [
            "shipping" => ['type' => 'string'],
            "asap" => ['type' => 'string', 'format' => 'date-time'],
            "today" => ['type' => 'boolean'],
            "fast" => ['type' => 'boolean'],
            "diff" => ['type' => 'string'],
            "choices" => ['type' => 'array', 'item' => ['type' => 'string', 'format' => 'date-time']],
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        $this->payments = new ArrayCollection();
        $this->events = new ArrayCollection();
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
        $this->customer = $customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getBusiness(): ?LocalBusiness
    {
        return  $this->business;
    }

    /**
     * @SerializedName("business")
     */
    public function setBusiness(?LocalBusiness $business): void
    {
        $this->business = $business;
    }


    public function hasBusiness(): bool
    {
        return null !== $this->getBusiness();
    }

    /**
     * @return float
     */
    public function getPercentageForBusiness(LocalBusiness $restaurant): float
    {
        $total = $this->getItemsTotal();

        if (0 === $total) {
            return 0.0;
        }

        $itemsTotal = $this->getItemsTotalForBusiness($restaurant);

        return round($itemsTotal / $total, 4);
    }

    /**
     * @return int
     */
    public function getItemsTotalForBusiness(LocalBusiness $restaurant): int
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            if ($restaurant->hasProduct($item->getVariant()->getProduct())) {
                $total += $item->getTotal();
            }
        }

        return $total;
    }


    /**
     * {@inheritdoc}
     */
    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @deprecated
     * @SerializedName("shippedAt")
     */
    public function getShippedAt(): ?\DateTime
    {
        if (null !== $this->shippingTimeRange) {

            $lower = Carbon::make($this->shippingTimeRange->getLower());
            $upper = Carbon::make($this->shippingTimeRange->getUpper());

            return $lower->average($upper)->toDateTime();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPayments(): bool
    {
        return !$this->payments->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function addPayment(PaymentInterface $payment): void
    {
        if (!$this->hasPayment($payment)) {
            $this->payments->add($payment);
            $payment->setOrder($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removePayment(PaymentInterface $payment): void
    {
        if ($this->hasPayment($payment)) {
            $this->payments->removeElement($payment);
            $payment->setOrder(null);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasPayment(PaymentInterface $payment): bool
    {
        return $this->payments->contains($payment);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastPayment(?string $state = null): ?PaymentInterface
    {
        if ($this->payments->isEmpty()) {
            return null;
        }

        // TODO Order payments by creation date

        $payment = $this->payments->filter(function (PaymentInterface $payment) use ($state): bool {
            return null === $state || $payment->getState() === $state;
        })->last();

        return $payment !== false ? $payment : null;
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function getChannel(): ?ChannelInterface
    {
        return $this->channel;
    }

    public function setChannel(?ChannelInterface $channel): void
    {
        $this->channel = $channel;
    }

    public function getShippingTimeRange(): ?TsRange
    {
        return $this->shippingTimeRange;
    }

    public function setShippingTimeRange(?TsRange $shippingTimeRange)
    {
        $this->shippingTimeRange = $shippingTimeRange;
    }

    /**
     * {@inheritdoc}
     */
    public function isTakeaway(): bool
    {
        // HOTFIX
        // Shit can happen when:
        // - only "collection" fulfillment method is available
        // - the order was saved in session with "delivery" fulfillment method

        if ($this->getState() === self::STATE_CART) {
            $restaurant = $this->getRestaurant();
            if (null !== $restaurant) {
                if (!$restaurant->isFulfillmentMethodEnabled('delivery') && $restaurant->isFulfillmentMethodEnabled('collection')) {
                    $this->setTakeaway(true);
                }
            }
        }

        return $this->takeaway;
    }

    public function setTakeaway(bool $takeaway): void
    {
        $this->takeaway = $takeaway;

        if ($takeaway) {
            $this->setShippingAddress(null);
        }
    }

    /**
     * @SerializedName("fulfillmentMethod")
     */
    public function getFulfillmentMethod(): string
    {
        return $this->isTakeaway() ? 'collection' : 'delivery';
    }

    /**
     * @SerializedName("paymentMethod")
     */
    public function getPaymentMethod(): string
    {
        $payment = $this->getLastPayment();

        if ($payment && $payment->getMethod()) {
            return $payment->getMethod()->getCode();
        }

        return '';
    }

    /**
     * @SerializedName("fulfillmentMethod")
     */
    public function setFulfillmentMethod(string $fulfillmentMethod)
    {
        $this->setTakeaway($fulfillmentMethod === 'collection');
    }

    public function getFulfillmentMethodObject(): ?FulfillmentMethod
    {
        $business = $this->getBusiness();

        if (null !== $business) {

            return $business->getFulfillmentMethod(
                $this->getFulfillmentMethod()
            );
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    /**
     * {@inheritdoc}
     */
    public function setDelivery(Delivery $delivery): void
    {
        $delivery->setOrder($this);

        $this->delivery = $delivery;
    }

    /**
     * @SerializedName("assignedTo")
     * @Groups({"order", "order_minimal"})
     */
    public function getAssignedTo()
    {
        if (null !== $this->getDelivery()) {
            $pickup = $this->getDelivery()->getPickup();

            if ($pickup->isAssigned()) {
                return $pickup->getAssignedCourier()->getUsername();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUser(): ?UserInterface
    {
        if (null === $this->customer) {
            return null;
        }

        if ($this->customer instanceof UserInterface) {
            return $this->customer;
        }

        return $this->customer->getUser();
    }

    public function getItemsGroupedByVendor(): \SplObjectStorage
    {
        $hash = new \SplObjectStorage();

        foreach ($this->getItems() as $item) {

            $product = $item->getVariant()->getProduct();
            $restaurant = $product->getRestaurant();

            if (null !== $restaurant) {
                $items = isset($hash[$restaurant]) ? $hash[$restaurant] : [];
                $hash[$restaurant] = array_merge($items, [ $item ]);
            }
        }

        return $hash;
    }

    public function getTransferAmount(LocalBusiness $business): int
    {
        $vendor = $this->getVendorByBusiness($business);

        if ($vendor) {

            return $vendor->getTransferAmount();
        }

        return 0;
    }

    public function getPickupAddress(): ?Address
    {
        if ($this->hasVendor()) {
            return $this->getVendor()->getAddress();
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function containsDisabledProduct(): bool
    {
        foreach ($this->getItems() as $item) {
            if ($item instanceof OrderItemInterface && !$item->getVariant()->getProduct()->isEnabled()) {

                return true;
            }
        }

        return false;
    }

}