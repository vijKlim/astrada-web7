<?php


namespace App\Enum;


use MyCLabs\Enum\Enum;

class OwnerType extends Enum
{
    const INDIVIDUAL = 'individual';//Власник —  звичайний користувач astrada
    const LEGAL_ENTITY = 'legal_entity';//Бізнес — це користувач, який використовує astrada для ведення бізнесу
}