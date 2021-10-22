<?php


namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ListingValidator extends ConstraintValidator
{
    public function validate($listing, Constraint $constraint)
    {
        //Duration
        if ($listing->getMinDuration() && $listing->getMaxDuration() &&
            $listing->getMinDuration() > $listing->getMaxDuration()
        ) {
            $this->context->buildViolation($constraint::$messageDuration)
                ->atPath('min_duration')
                ->addViolation();
        }
    }
}