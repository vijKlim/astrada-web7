<?php


namespace App\Service;


use App\Entity\Address;
use App\Entity\Model\UserAddressRequest;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UserSearchAddresses
{
    private $session;
    private $userAddressRequest;

    public function __construct(SessionInterface $session, UserAddressRequest $userAddressRequest)
    {
        $this->session = $session;
        $this->userAddressRequest = $userAddressRequest;
    }

    /**
     * @return Address
     */
    public function getLastUserSearchAddress()
    {
        $userAddressRequest = $this->getUserAddressRequest();
        return $userAddressRequest->getAddress();
    }

    /**
     * @return UserAddressRequest
     */
    public function getUserAddressRequest()
    {

        /** @var UserAddressRequest $userAddressRequest */
        $userAddressRequest = $this->session->has('user_address_request') ?
            $this->session->get('user_address_request') :
            $this->userAddressRequest;

        return $userAddressRequest;
    }
}