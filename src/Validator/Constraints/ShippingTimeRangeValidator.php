<?php


namespace App\Validator\Constraints;


use App\Sylius\Order\OrderInterface;
use Carbon\Carbon;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ShippingTimeRangeValidator extends ConstraintValidator
{
    private $shippingDateFilter;
    private $orderTimeHelper;

    public function __construct()
    {
    }

    public function validate($value, Constraint $constraint)
    {
        $object = $this->context->getObject();

        if (null === $object || !$object instanceof OrderInterface) {
            throw new UnexpectedValueException($object, OrderInterface::class);
        }

        // WARNING
        // We use Carbon to be able to mock the date in Behat tests
        $now = Carbon::now();

        $isNew = $object->getId() === null || $object->getState() === OrderInterface::STATE_CART;

        if (!$isNew) {
            return;
        }

        // A new order with empty time range is valid,
        // as long as there is at least a future choice
        if (null === $value) {

//            $range = $this->orderTimeHelper->getShippingTimeRange($object);
//
//            if (null == $range) {
//                $this->context
//                    ->buildViolation($constraint->shippingTimeRangeNotAvailableMessage)
//                    ->setCode(ShippingTimeRange::SHIPPING_TIME_RANGE_NOT_AVAILABLE)
//                    ->addViolation();
//            }
        }else{

        }
    }
}