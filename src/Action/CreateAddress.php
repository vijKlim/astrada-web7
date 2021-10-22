<?php


namespace App\Action;

use App\Action\Utils\TokenStorageTrait;
use App\Entity\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CreateAddress
{
    use TokenStorageTrait;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function __invoke($data): Address
    {
        $user = $this->getUser();

        $address = $data;

        $user->addAddress($address);

        return $data;
    }
}