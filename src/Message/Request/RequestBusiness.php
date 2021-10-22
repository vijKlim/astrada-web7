<?php
declare(strict_types=1);

namespace App\Message\Request;

use App\Entity\Address;

class RequestBusiness
{
    private string $name;
    /**
     * @var Address
     */
    private Address $address;
    private string $contact;

    public function __construct(string $name, Address $address, string $contact)
    {
        $this->name = $name;
        $this->address = $address;
        $this->contact = $contact;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getContact(): string
    {
        return $this->contact;
    }
}
