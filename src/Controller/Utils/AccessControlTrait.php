<?php


namespace App\Controller\Utils;


use App\Entity\LocalBusiness;

trait AccessControlTrait
{
    /**
     * @var LocalBusiness $object
     */
    protected function accessControl($object)
    {
        if ($object instanceof LocalBusiness) {
            $this->denyAccessUnlessGranted('edit', $object);
        }

//        if ($object instanceof Store) {
//            $this->denyAccessUnlessGranted('edit', $object);
//        }
    }
}