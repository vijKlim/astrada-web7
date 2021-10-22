<?php

namespace App\Service\Routing;

use App\Service\RoutingInterface;
use App\Entity\Base\GeoCoordinates;
use Polyline;

abstract class Base implements RoutingInterface
{
    public function getPoints(GeoCoordinates ...$coordinates)
    {
        $polyline = $this->getPolyline(...$coordinates);
        $points = Polyline::decode($polyline);

        return Polyline::pair($points);
    }
}
