<?php


namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Listing extends Constraint
{
    public static $messageMaxImages = "listing_images.max {{ max_images }}";
    public static $messageMinImages = "listing_images.min {{ min_images }}";
    public static $messageMinCategories = "listing_categories.min {{ min_categories }}";
//    public static $messageStatusInvalidated = "listing_status.invalidated";
    public static $messageMinPrice = "listing_price.min {{ min_price }}";
    public static $messageDuration = "listing_duration.overlap";
    public static $messageCountryInvalid = "listing_location_country.invalid";

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}