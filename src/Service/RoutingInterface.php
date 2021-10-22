<?php

namespace App\Service;

use App\Entity\Base\GeoCoordinates;

interface RoutingInterface
{
    /**
     * @param GeoCoordinates[] ...$coordinates
     */
    public function getPolyline(GeoCoordinates ...$coordinates);

    /**
     * @param GeoCoordinates[] ...$coordinates
     */
    public function getPoints(GeoCoordinates ...$coordinates);

    /**
     * @param GeoCoordinates[] ...$coordinates
     */
    public function getDistance(GeoCoordinates ...$coordinates);

    /**
     * @param GeoCoordinates[] ...$coordinates
     */
    public function getDuration(GeoCoordinates ...$coordinates);
}
