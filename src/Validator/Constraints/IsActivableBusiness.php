<?php


namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsActivableBusiness extends Constraint
{
    public $enabledMessage = 'business.notActivable';
    public $nameMessage = 'business.name.notBlank';
    public $telephoneMessage = 'business.telephone.notBlank';
    public $openingHoursMessage = 'business.openingHours.notBlank';
    public $contractMessage = 'business.contract.notValid';
    public $stripeAccountMessage = 'business.stripeAccount.notSet';

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}