<?php


namespace Astrada\SubscriptionBundle;


use Astrada\SubscriptionBundle\DependencyInjection\AstradaSubscriptionExtension;
use Astrada\SubscriptionBundle\DependencyInjection\Compiler\ServicesCompilerPass;
use Astrada\SubscriptionBundle\DependencyInjection\Compiler\SubscriptionStrategyCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

//https://scriptun.com/symfony/create-local-bundle
class AstradaSubscriptionBundle extends Bundle
{
    const COMMAND_NAMESPACE = 'astrada:subscription';

    /**
     *{@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SubscriptionStrategyCompilerPass());
        $container->addCompilerPass(new ServicesCompilerPass());

    }
}