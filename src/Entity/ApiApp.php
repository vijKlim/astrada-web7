<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\LocalBusiness;
use Gedmo\Timestampable\Traits\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @see https://schema.org/SoftwareApplication Documentation on Schema.org
 *
 * @ApiResource(iri="http://schema.org/SoftwareApplication",
 *   itemOperations={
 *     "get"={
 *       "method"="GET",
 *       "security"="is_granted('ROLE_ADMIN')"
 *     }
 *   },
 *   collectionOperations={},
 *   attributes={
 *     "normalization_context"={"groups"={"api_app"}},
 *   }
 * )
 */
class ApiApp
{
    use Timestampable;

    private $id;

    /**
     * @var string
     * @Groups({"api_app"})
     */
    private $name;



    /**
     * @var LocalBusiness
     * @Groups({"api_app"})
     */
    private $business;

    /**
     * @var string
     */
    private $type = 'oauth';

    /**
     * @var string|null
     */
    private $apiKey;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }



    public function getBusiness(): ?LocalBusiness
    {
        return $this->business;
    }

    public function setBusiness(LocalBusiness $business)
    {
        $this->business = $business;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }
}
