<?php


namespace App\Service\Routing;


use App\Entity\Base\GeoCoordinates;
use GuzzleHttp\Client;

class Google extends Base
{
    /**
     * @var Client
     */
    private $client;

    //google api key
    private $key;

    private $cache = [];

    /**
     * @param Client $client
     */
    public function __construct(Client $client, $key)
    {
        $this->client = $client;
        $this->key = $key;
    }


    public function getServiceResponse($service, array $coordinates, array $options = [])
    {
        $coords = array_map(function($coordinate) {
            // String of format {longitude},{latitude}|{longitude},{latitude}[|{longitude},{latitude} ...] or polyline({polyline}) or polyline6({polyline6}) .
            return implode(',', [ $coordinate->getLatitude(), $coordinate->getLongitude()  ]);
        }, $coordinates);

        $origin = array_pop($coords);
        $destinations = implode('|', $coords);


        $cacheKey = sprintf('%s://%s', $origin, $destinations);


        if (!isset($this->cache[$cacheKey])) {
            $uri = "/maps/api/distancematrix/json?&key={$this->key}&origins={$origin}&destinations={$destinations}&mode=driving&language=uk-UA";
            $response = $this->client->request('GET', $uri);
            $data = json_decode($response->getBody(), true);

            $this->cache[$cacheKey] = $data;
        }

        return $this->cache[$cacheKey];
    }

    public function getPolyline(GeoCoordinates ...$coordinates)
    {
        // TODO: Implement getPolyline() method.
    }

    public function getDistance(GeoCoordinates ...$coordinates)
    {
        $response = $this->getServiceResponse('route', $coordinates, ['overview' => 'full']);

        //return  (int)$response['rows'][0]['elements'][0]['distance']['text'];

        // in meters
        return  (int)$response['rows'][0]['elements'][0]['distance']['value'];
    }

    public function getDuration(GeoCoordinates ...$coordinates)
    {
        $response = $this->getServiceResponse('route', $coordinates, ['overview' => 'full']);

        // in sec
        return (int)$response['rows'][0]['elements'][0]['duration']['value'];
    }

}