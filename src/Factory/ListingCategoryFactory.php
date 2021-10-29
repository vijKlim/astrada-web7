<?php


namespace App\Factory;


use Sylius\Component\Resource\Exception\UnexpectedTypeException;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Factory\TranslatableFactoryInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Translation\Provider\TranslationLocaleProviderInterface;

class ListingCategoryFactory implements TranslatableFactoryInterface
{
    /** @var FactoryInterface */
    private $factory;

    /** @var TranslationLocaleProviderInterface */
    private $localeProvider;

    public function __construct(FactoryInterface $factory, TranslationLocaleProviderInterface $localeProvider)
    {
        $this->factory = $factory;
        $this->localeProvider = $localeProvider;
    }

    /**
     * @throws UnexpectedTypeException
     */
    public function createNew()
    {
        $resource = $this->factory->createNew();

        if (!$resource instanceof TranslatableInterface) {
            throw new UnexpectedTypeException($resource, TranslatableInterface::class);
        }

        $resource->setCurrentLocale($this->localeProvider->getDefaultLocaleCode());
        $resource->setFallbackLocale($this->localeProvider->getDefaultLocaleCode());

        return $resource;
    }

}