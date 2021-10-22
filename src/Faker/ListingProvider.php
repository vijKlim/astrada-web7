<?php


namespace App\Faker;

use Faker\Provider\Base as BaseProvider;

class ListingProvider extends BaseProvider
{

    protected static $listingFormats = array(
        '{{listingPrefix}} {{listingAdjective}} {{listingSuffix}}',
        '{{listingPrefix}} {{listingSuffix}}',
    );

    protected static $listingPrefixes = array(
        'water','water-clear','art-water','well','water well','water-well'
    );

    protected static $listingAdjectives = array(
        'blue', 'black',
        'white','yellow'
    );

    protected static $listingSuffixes = array(
        'drill','service','company','org', 'group','equipment','machine',''
    );

    public function listingName()
    {
        $format = static::randomElement(static::$listingFormats);

        return ucfirst($this->generator->parse($format));
    }

    public function listingPrefix()
    {
        return static::randomElement(static::$listingPrefixes);
    }

    public function listingAdjective()
    {
        return static::randomElement(static::$listingAdjectives);
    }

    public function listingSuffix()
    {
        return static::randomElement(static::$listingSuffixes);
    }

}