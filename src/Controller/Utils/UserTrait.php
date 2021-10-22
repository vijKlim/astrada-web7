<?php


namespace App\Controller\Utils;


use App\Entity\Address;

trait UserTrait
{
    protected function getUserAddresses()
    {
        $addresses = [];

        $user = $this->getUser();
        if ($user) {
            $addresses = $user->getAddresses()->toArray();
        }

        return array_map(function ($address) {

            return $this->get('serializer')->normalize($address, 'jsonld', [
                'resource_class' => Address::class,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
                'groups' => ['address']
            ]);
        }, $addresses);
    }
}