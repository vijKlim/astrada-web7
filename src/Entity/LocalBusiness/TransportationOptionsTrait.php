<?php


namespace App\Entity\LocalBusiness;


use App\Entity\Address;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;


trait TransportationOptionsTrait
{
    /**
     * @var string
     *
     * @Assert\Type(type="string")
     */
    protected $transportationPerimeterExpression = 'distance < 300';



    /**
     * @return string
     */
    public function getTransportationPerimeterExpression()
    {
        return $this->transportationPerimeterExpression;
    }

    /**
     * @param string $transportationPerimeterExpression
     */
    public function setDeliveryPerimeterExpression(string $transportationPerimeterExpression)
    {
        $this->transportationPerimeterExpression = $transportationPerimeterExpression;
    }

    public function canDeliverAddress(Address $address, $distance, ExpressionLanguage $language = null)
    {
        if (null === $language) {
            $language = new ExpressionLanguage();
        }

        $dropoff = new \stdClass();
        $dropoff->address = $address;

        return $language->evaluate($this->transportationPerimeterExpression, [
            'distance' => $distance,
            'dropoff' => $dropoff,
        ]);
    }
}