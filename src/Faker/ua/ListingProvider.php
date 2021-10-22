<?php


namespace App\Faker\ua;

use App\Faker\ListingProvider as BaseListingProvider;

class ListingProvider extends BaseListingProvider
{
    protected static $listingPrefixes = array(
        'Ukrbur','Укрбур','Вода', 'Вода-прозора', 'Арт-вода', 'Криниця', 'Криниця води', 'Криниця води','Свердловина','Водна свердловина'
    );

    protected static $listingAdjectives = array(
        'біла', 'жовта','exclusive','AI','good','smart','best','pr.'
    );

    protected static $listingSuffixes = array(
        'бурити', 'сервіс', 'компанія', 'організація', 'група', 'обладнання', 'машини'
    );
}