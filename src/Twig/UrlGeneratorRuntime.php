<?php


namespace App\Twig;

use App\Entity\Listing;
use App\Entity\LocalBusiness;
use App\Enum\Store;
use Cocur\Slugify\SlugifyInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\RuntimeExtensionInterface;

class UrlGeneratorRuntime implements RuntimeExtensionInterface
{
    private $urlGenerator;
    private $slugify;

    public function __construct(UrlGeneratorInterface $urlGenerator, SlugifyInterface $slugify)
    {
        $this->urlGenerator = $urlGenerator;
        $this->slugify = $slugify;
    }

    public function localBusinessPath(LocalBusiness $entity, $parameters, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $defaultParameters = [
            'id' => $entity->getId(),
            'slug' => $this->slugify->slugify($entity->getName()),
            'type' => $entity->getContext() === Store::class ? 'store' : 'business',
        ];

        return $this->urlGenerator->generate('business', array_merge($defaultParameters, $parameters), $referenceType);
    }

    public function listingPath(Listing $entity, $parameters, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $defaultParameters = [
            'id' => $entity->getId(),
            'slug' => $this->slugify->slugify($entity->getTitle()),
        ];

        return $this->urlGenerator->generate('listing', array_merge($defaultParameters, $parameters), $referenceType);
    }
}