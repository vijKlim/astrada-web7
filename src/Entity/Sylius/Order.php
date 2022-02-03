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
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\Order as BaseOrder;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see http://schema.org/Order Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/Order",
 *   collectionOperations={
 *     "get"={
 *       "method"="GET",
 *       "security"="is_granted('ROLE_ADMIN')"
 *     },
 *     "post"={
 *       "method"="POST",
 *       "denormalization_context"={"groups"={"order_create", "address_create"}}
 *     },
 *     "timing"={
 *       "method"="POST",
 *       "path"="/orders/timing",
 *       "write"=false,
 *       "status"=200,
 *       "denormalization_context"={"groups"={"order_create", "address_create"}},
 *       "normalization_context"={"groups"={"cart_timing"}},
 *       "openapi_context"={
 *         "summary"="Retrieves timing information about a Order resource.",
 *         "responses"={
 *           "200"={
 *             "description"="Order timing information",
 *             "content"={
 *               "application/json": {
 *                 "schema"=Order::SWAGGER_CONTEXT_TIMING_RESPONSE_SCHEMA
 *               }
 *             }
 *           }
 *         }
 *       }
 *     },
 *     "my_orders"={
 *       "method"="GET",
 *       "path"="/me/orders",
 *       "controller"=MyOrders::class
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "security"="is_granted('view', object)"
 *     },
 *     "payment_details"={
 *       "method"="GET",
 *       "path"="/orders/{id}/payment",
 *       "controller"=PaymentDetailsController::class,
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Get payment details for a Order resource."
 *       }
 *     },
 *     "payment_methods"={
 *       "method"="GET",
 *       "path"="/orders/{id}/payment_methods",
 *       "controller"=PaymentMethodsController::class,
 *       "output"=PaymentMethodsOutput::class,
 *       "normalization_context"={"api_sub_level"=true},
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Get available payment methods for a Order resource."
 *       }
 *     },
 *     "pay"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/pay",
 *       "controller"=OrderPay::class,
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Pays a Order resource."
 *       }
 *     },
 *     "accept"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/accept",
 *       "controller"=OrderAccept::class,
 *       "security"="is_granted('accept', object)",
 *       "deserialize"=false,
 *       "openapi_context"={
 *         "summary"="Accepts a Order resource."
 *       }
 *     },
 *     "refuse"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/refuse",
 *       "controller"=OrderRefuse::class,
 *       "security"="is_granted('refuse', object)",
 *       "openapi_context"={
 *         "summary"="Refuses a Order resource."
 *       }
 *     },
 *     "delay"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/delay",
 *       "controller"=OrderDelay::class,
 *       "security"="is_granted('delay', object)",
 *       "openapi_context"={
 *         "summary"="Delays a Order resource."
 *       }
 *     },
 *     "fulfill"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/fulfill",
 *       "controller"=OrderFulfill::class,
 *       "security"="is_granted('fulfill', object)",
 *       "openapi_context"={
 *         "summary"="Fulfills a Order resource."
 *       }
 *     },
 *     "cancel"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/cancel",
 *       "controller"=OrderCancel::class,
 *       "security"="is_granted('cancel', object)",
 *       "openapi_context"={
 *         "summary"="Cancels a Order resource."
 *       }
 *     },
 *     "assign"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/assign",
 *       "controller"=OrderAssign::class,
 *       "validation_groups"={"cart"},
 *       "normalization_context"={"groups"={"cart"}},
 *       "openapi_context"={
 *         "summary"="Assigns a Order resource to a User."
 *       }
 *     },
 *     "get_cart_timing"={
 *       "method"="GET",
 *       "path"="/orders/{id}/timing",
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Retrieves timing information about a Order resource.",
 *         "responses"={
 *           "200"={
 *             "description"="Order timing information",
 *             "content"={
 *               "application/json": {
 *                 "schema"=Order::SWAGGER_CONTEXT_TIMING_RESPONSE_SCHEMA
 *               }
 *             }
 *           }
 *         }
 *       }
 *     },
 *     "validate"={
 *       "method"="GET",
 *       "path"="/orders/{id}/validate",
 *       "normalization_context"={"groups"={"cart"}},
 *       "security"="is_granted('session', object)"
 *     },
 *     "put_cart"={
 *       "method"="PUT",
 *       "path"="/orders/{id}",
 *       "validation_groups"={"cart"},
 *       "normalization_context"={"groups"={"cart"}},
 *       "denormalization_context"={"groups"={"order_update"}},
 *       "security"="is_granted('session', object)"
 *     },
 *     "post_cart_items"={
 *       "method"="POST",
 *       "path"="/orders/{id}/items",
 *       "input"=CartItemInput::class,
 *       "controller"=AddCartItem::class,
 *       "validation_groups"={"cart"},
 *       "denormalization_context"={"groups"={"cart"}},
 *       "normalization_context"={"groups"={"cart"}},
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Adds items to a Order resource."
 *       }
 *     },
 *     "put_item"={
 *       "method"="PUT",
 *       "path"="/orders/{id}/items/{itemId}",
 *       "controller"=UpdateCartItem::class,
 *       "validation_groups"={"cart"},
 *       "denormalization_context"={"groups"={"cart"}},
 *       "normalization_context"={"groups"={"cart"}},
 *       "security"="is_granted('session', object)"
 *     },
 *     "delete_item"={
 *       "method"="DELETE",
 *       "path"="/orders/{id}/items/{itemId}",
 *       "controller"=DeleteCartItem::class,
 *       "validation_groups"={"cart"},
 *       "normalization_context"={"groups"={"cart"}},
 *       "validate"=false,
 *       "write"=false,
 *       "status"=200,
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Deletes items from a Order resource."
 *       }
 *     },
 *     "centrifugo"={
 *       "method"="GET",
 *       "path"="/orders/{id}/centrifugo",
 *       "controller"=CentrifugoController::class,
 *       "normalization_context"={"groups"={"centrifugo", "centrifugo_for_order"}},
 *       "security"="is_granted('view', object)",
 *       "openapi_context"={
 *         "summary"="Get Centrifugo connection details for a Order resource."
 *       }
 *     },
 *     "mercadopago_preference"={
 *       "method"="GET",
 *       "path"="/orders/{id}/mercadopago-preference",
 *       "controller"=MercadopagoPreference::class,
 *       "output"=MercadopagoPreferenceResponse::class,
 *       "security"="is_granted('session', object)",
 *       "openapi_context"={
 *         "summary"="Creates a MercadoPago preference and returns its ID."
 *       }
 *     }
 *   },
 *   attributes={
 *     "denormalization_context"={"groups"={"order_create"}},
 *     "normalization_context"={"groups"={"order", "address"}}
 *   }
 * )
 * @ApiFilter(OrderDateFilter::class, properties={"date": "exact"})
 *
 * @AssertOrder(groups={"Default"})
 * @AssertOrderIsModifiable(groups={"cart"})
 * @AssertLoopEatOrder(groups={"loopeat"})
 */
class Order extends BaseOrder implements OrderInterface
{
    protected $customer;

    protected $vendor;

    /**
     * @Assert\Valid
     * @AssertShippingAddress
     */
    protected $shippingAddress;

    protected $billingAddress;

    protected $payments;

    protected $delivery;

    protected $events;

    protected $timeline;

    protected $channel;

    protected $promotionCoupon;

    protected $promotions;

    protected $reusablePackagingEnabled = false;

    protected $reusablePackagingPledgeReturn = 0;

    protected $receipt;

    /**
     * @var int|null
     */
    protected $tipAmount = null;

    /**
     * @AssertShippingTimeRange(groups={"Default", "ShippingTime"})
     */
    protected $shippingTimeRange;


    protected $nonprofit;


    /**
     * @Assert\Expression(
     *   "!this.isTakeaway() or (this.isTakeaway() and this.getRestaurant().isFulfillmentMethodEnabled('collection'))",
     *   message="order.collection.not_available",
     *   groups={"cart"}
     * )
     */
    protected $takeaway = false;

    protected $vendors;

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
        $this->promotions = new ArrayCollection();
        $this->vendors = new ArrayCollection();
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
        if (null === $this->vendor) {

            return null;
        }

        return $this->vendor->getBusiness();
    }

    /**
     * @SerializedName("business")
     */
    public function setBusiness(?LocalBusiness $business): void
    {
        $currentBusiness = $this->getBusiness();

        $vendor = new Vendor();
        $vendor->setBusiness($business);

        $this->vendor = $vendor;

        if (null !== $business && $business !== $currentBusiness) {

            $this->vendors->clear();

            $this->clearItems();
            $this->setShippingTimeRange(null);

            $this->addBusiness($business);
        }
    }

    public function addBusiness(LocalBusiness $business, int $itemsTotal = 0, int $transferAmount = 0)
    {
        $vendor = $this->getVendorByBusiness($business);

        if (null === $vendor) {
            $vendor = new OrderVendor($this, $business);
            $this->vendors->add($vendor);
        }

        $vendor->setItemsTotal($itemsTotal);
        $vendor->setTransferAmount($transferAmount);
    }



    public function getVendorByBusiness(LocalBusiness $business): ?OrderVendor
    {
        foreach ($this->vendors as $vendor) {
            if ($vendor->getBusiness() === $business) {
                return $vendor;
            }
        }

        return null;
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

    public function hasVendor(): bool
    {
        return null !== $this->getVendor();
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

    public function getVendor(): ?Vendor
    {
        return $this->vendor;
    }

    public function setVendor(?Vendor $vendor): void
    {
        $this->vendor = $vendor;
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