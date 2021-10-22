<?php


namespace App\Entity\LocalBusiness;


use Doctrine\ORM\QueryBuilder;

interface FindNearbyInterface
{

    public function countNearby($latitude, $longitude, $distance = 5000, $limit = 10, $offset = 0);

    public function findNearby($latitude, $longitude, $distance = 5000, $limit = 10, $offset = 0);
}