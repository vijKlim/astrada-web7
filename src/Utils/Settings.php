<?php

namespace App\Utils;

use Symfony\Component\Validator\Constraints as Assert;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;

class Settings
{
    public $brand_name;

    public $administrator_email;

    /**
     * @AssertPhoneNumber
     */
    public $phone_number;


    public $sms_enabled;

    public $sms_gateway;

    public $sms_gateway_config;


    public $google_api_key;

    public $latlng;

    public $subject_to_vat;

    public $currency_code;

    public $guest_checkout_enabled;

}
