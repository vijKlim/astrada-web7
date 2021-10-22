<?php


namespace App\Faker\ua;

use App\Faker\BusinessProvider as BaseBusinessProvider;

class BusinessProvider extends BaseBusinessProvider
{
    protected static $businessPrefixes = array(
        'Ukrbur','Укрбур','Вода', 'Вода-прозора', 'Арт-вода', 'Криниця', 'Криниця води', 'Криниця води','Свердловина','Водна свердловина'
    );

    protected static $businessAdjectives = array(
        'біла', 'жовта','exclusive','AI','good','smart','best','pr.'
    );

    protected static $businessSuffixes = array(
        'бурити', 'сервіс', 'компанія', 'організація', 'група', 'обладнання', 'машини'
    );
}