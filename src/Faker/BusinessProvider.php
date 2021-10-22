<?php


namespace App\Faker;

use Faker\Provider\Base as BaseProvider;

class BusinessProvider extends BaseProvider
{

    protected static $businessFormats = array(
        '{{businessPrefix}} {{businessAdjective}} {{businessSuffix}}',
        '{{businessPrefix}} {{businessSuffix}}',
    );

    protected static $businessPrefixes = array(
        'water','water-clear','art-water','well','water well','water-well'
    );

    protected static $businessAdjectives = array(
        'blue', 'black',
        'white','yellow'
    );

    protected static $businessSuffixes = array(
        'drill','service','company','org', 'group','equipment','machine',''
    );

    public function businessName()
    {
        $format = static::randomElement(static::$businessFormats);

        return ucfirst($this->generator->parse($format));
    }

    public function businessPrefix()
    {
        return static::randomElement(static::$businessPrefixes);
    }

    public function businessAdjective()
    {
        return static::randomElement(static::$businessAdjectives);
    }

    public function businessSuffix()
    {
        return static::randomElement(static::$businessSuffixes);
    }

}