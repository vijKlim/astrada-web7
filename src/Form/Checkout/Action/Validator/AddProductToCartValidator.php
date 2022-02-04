<?php


namespace App\Form\Checkout\Action\Validator;


use App\Sylius\Cart\BusinessResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;

class AddProductToCartValidator
    extends ConstraintValidator
{
    private $resolver;

    public function __construct(BusinessResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function validate($value, Constraint $constraint)
    {
        $business = $this->resolver->resolve();

        if (null === $business) {
            throw new LogicException('No business could be resolved from request.');
        }

        if (!$value->product->isEnabled()) {
            $this->context
                ->buildViolation($constraint->productDisabled)
                ->atPath('items')
                ->setParameter('%code%', $value->product->getCode())
                ->addViolation();

            return;
        }

        if (!$business->hasProduct($value->product)) {
            $this->context
                ->buildViolation($constraint->productNotBelongsTo)
                ->atPath('business')
                ->setParameter('%code%', $value->product->getCode())
                ->addViolation();

            return;
        }

        if (!$this->resolver->accept($value->cart) && !$value->clear) {
            $this->context
                ->buildViolation($constraint->notSameBusiness)
                ->atPath('business')
                ->addViolation();

            return;
        }
    }
}