<?php


namespace App\Enum;


use MyCLabs\Enum\Enum;

/**
 * A construction business. A HomeAndConstructionBusiness is a LocalBusiness that provides services around homes and buildings.
 *
 * @see http://schema.org/LocalBusiness Documentation on Schema.org
 */
class HomeAndConstructionBusiness extends Enum
{
    const GENERAL_CONTRACTOR = 'https://schema.org/GeneralContractor';
}