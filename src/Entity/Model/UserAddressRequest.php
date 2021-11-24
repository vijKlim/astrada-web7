<?php


namespace App\Entity\Model;



use Symfony\Component\HttpFoundation\RequestStack;

class UserAddressRequest
{

    protected $geohash;
    protected $streetAddress;
    protected $addressLocality;
    protected $latitude;
    protected $longitude;
    protected $postalCode;


    /**
     * @param RequestStack $requestStack
     * @param int          $maxPerPage
     */
    public function __construct(RequestStack $requestStack)
    {

        $request = $requestStack->getCurrentRequest();
        if ($request) {
            $this->locale = $request->getLocale();
            if ($request->isXmlHttpRequest()) {
                $this->isXmlHttpRequest = true;
            }
        }
        $content  = json_decode($request->getContent(),true);

        $this->geohash = $content["geohash"] ?? null;
        $this->streetAddress = $content["streetAddress"] ?? null;
        $this->addressLocality = $content["addressLocality"] ?? null;
        $this->postalCode = $content["postalCode"] ?? null;
        $this->latitude = $content["latitude"] ?? null;
        $this->longitude = $content["longitude"] ?? null;

    }

    /**
     * @return bool|float|int|string|null
     */
    public function getGeohash()
    {
        return $this->geohash;
    }

    /**
     * @param bool|float|int|string|null $geohash
     */
    public function setGeohash($geohash): void
    {
        $this->geohash = $geohash;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function getStreetAddress()
    {
        return $this->streetAddress;
    }

    /**
     * @param bool|float|int|string|null $streetAddress
     */
    public function setStreetAddress($streetAddress): void
    {
        $this->streetAddress = $streetAddress;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function getAddressLocality()
    {
        return $this->addressLocality;
    }

    /**
     * @param bool|float|int|string|null $addressLocality
     */
    public function setAddressLocality($addressLocality): void
    {
        $this->addressLocality = $addressLocality;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param bool|float|int|string|null $latitude
     */
    public function setLatitude($latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param bool|float|int|string|null $longitude
     */
    public function setLongitude($longitude): void
    {
        $this->longitude = $longitude;
    }

    /**
     * @return bool|float|int|string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param bool|float|int|string|null $postalCode
     */
    public function setPostalCode($postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Remove some Object properties while serialisation
     *
     * @return array
     */
    public function __sleep()
    {
        return array_diff(array_keys(get_object_vars($this)), array('requestStack', 'request'));
    }
}