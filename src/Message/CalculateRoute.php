<?php


namespace App\Message;


class CalculateRoute
{
    private $addressId;

    public function __construct($addressId)
    {
        $this->addressId = $addressId;
    }

    public function getAddressId()
    {
        return $this->addressId;
    }
}