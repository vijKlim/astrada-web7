<?php


namespace App\Validator\Constraints;


use App\Service\RoutingInterface;
use App\Sylius\Order\OrderInterface;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ShippingAddressValidator extends ConstraintValidator
{
    private $routing;
    private $expressionLanguage;

    public function __construct(
        RoutingInterface $routing,
        ExpressionLanguage $expressionLanguage
    )
    {
        $this->routing = $routing;
        $this->expressionLanguage = $expressionLanguage;
    }

    public function validate($value, Constraint $constraint)
    {
        $object = $this->context->getObject();

        if(null === $object || !$object instanceof OrderInterface){
            throw new UnexpectedValueException($object, OrderInterface::class);
        }

        $isNew = $object->getId() === null || $object->getState() === OrderInterface::STATE_CART;

        if(!$isNew){
            return;
        }

        if(!$object->hasBusiness()){
            return;
        }

        $itemsTotal = $object->getItemsTotal();

        // Stop here when order is empty
        // We don't want to show an error on shipping address until at least one item is added
        if ($itemsTotal === 0) {
            return;
        }

        if(null === $value){
            $this->context->buildViolation($constraint->addressNotSetMessage)
                ->setCode(ShippingAddress::ADDRESS_NOT_SET)
                ->addViolation();

            return;
        }

        $business = $object->getBusiness();

        $distance = $this->routing->getDistance(
            $object->getPickupAddress()->getGeo(),
            $value->getGeo()
        );

        if(!$business->canDeliverAddress($value, $distance, $this->expressionLanguage)){
            $this->context->buildViolation($constraint->addressTooFarMessage)
                ->setCode(ShippingAddress::ADDRESS_TOO_FAR)
                ->addViolation();

            return;
        }
    }
}