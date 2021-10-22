<?php


namespace App\Entity\Listener;

use App\Entity\Address;
use App\Message\CalculateRoute;
use App\Utils\GeoUtils;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class AddressListener
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function preUpdate(Address $address, PreUpdateEventArgs $event)
    {
        if($event->hasChangedField('streetAddress') || $event->hasChangedField('geo'))
        {
            // Make sure to ignore the SRID
            // $oldValue = "SRID=4326;POINT(2.309128 48.872815)"
            // $newValue = "POINT(2.309128 48.872815)"
            if($event->hasChangedField('geo')) {
                $oldValue = $event->getOldValue('geo');
                $newValue = $event->getNewValue('geo');
                if (!empty($oldValue) && !empty($newValue)) {
                    $oldValueAsObject = GeoUtils::asGeoCoordinates($oldValue);
                    $newValueAsObject = GeoUtils::asGeoCoordinates($newValue);
                    if ($oldValueAsObject->isEqualTo($newValueAsObject)) {
                        return;
                    }
                }
            }

            $this->messageBus->dispatch(
                new CalculateRoute($address->getId())
            );
        }
    }
}