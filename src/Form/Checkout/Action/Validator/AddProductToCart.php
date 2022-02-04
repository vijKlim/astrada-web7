<?php


namespace App\Form\Checkout\Action\Validator;

use Symfony\Component\Validator\Constraint;

class AddProductToCart extends Constraint
{
    public $productDisabled = 'Product %code% is not enabled';
    public $productNotBelongsTo = 'Unable to add product %code%';
    public $notSameBusiness = 'Business mismatch';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}