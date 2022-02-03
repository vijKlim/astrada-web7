<?php


namespace App\Entity\Sylius;


use App\Entity\LocalBusiness;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderAwareInterface;

class OrderVendor implements OrderAwareInterface
{
    protected $order;
    protected $business;
    protected $itemsTotal = 0;
    protected $transferAmount = 0;

    public function __construct(OrderInterface $order, LocalBusiness $business)
    {
        $this->order = $order;
        $this->business = $business;
    }

    /**
     * @return OrderInterface
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @param OrderInterface $order
     */
    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
    }

    /**
     * @return LocalBusiness
     */
    public function getBusiness(): LocalBusiness
    {
        return $this->business;
    }

    /**
     * @param LocalBusiness $business
     */
    public function setBusiness(LocalBusiness $business): void
    {
        $this->business = $business;
    }

    /**
     * @return int
     */
    public function getItemsTotal(): int
    {
        return $this->itemsTotal;
    }

    /**
     * @param int $itemsTotal
     */
    public function setItemsTotal(int $itemsTotal)
    {
        $this->itemsTotal = $itemsTotal;

        return $this;
    }

    /**
     * @return int
     */
    public function getTransferAmount(): int
    {
        return $this->transferAmount;
    }

    /**
     * @param int $transferAmount
     */
    public function setTransferAmount(int $transferAmount)
    {
        $this->transferAmount = $transferAmount;

        return $this;
    }


}