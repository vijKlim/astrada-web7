<?php


namespace App\Api\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

final class BusinessInput
{

    /**
     * @Groups({"business_update"})
     */
    public $state;
}