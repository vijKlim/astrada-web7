<?php


namespace App\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ServicesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $this->processFactories($container);
    }

    protected function processFactories(ContainerBuilder $container)
    {
        $topicFactoryDefinition = $container->getDefinition('app.factory.topic');
        $topicFactoryDefinition
            ->addArgument(new Reference('app.context.customer'))
            ->addArgument(new Reference('app.factory.post'));

        $postFactoryDefinition = $container->getDefinition('app.factory.post');
        $postFactoryDefinition
            ->addArgument(new Reference('app.context.customer'));

        $listingCategoryDefinition = $container->getDefinition('app.factory.listing_category');
    }
}