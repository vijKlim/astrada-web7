<?php

namespace App\Entity\Sylius;


use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderAwareInterface;
use Sylius\Component\Payment\Model\Payment as BasePayment;

class Payment extends BasePayment implements OrderAwareInterface
{

    protected $order;

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
    }

    public function isCashOnDelivery(): bool
    {
        $method = $this->getMethod();

        return null !== $method && $method->getCode() === 'CASH_ON_DELIVERY';
    }
}
