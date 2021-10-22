<?php

namespace Astrada\SubscriptionBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;


class ServicesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $productRepository      = $container->getParameter('astrada_subscription.config.product.repository');
        $subscriptionRepository = $container->getParameter('astrada_subscription.config.subscription.repository');

        $container->setAlias(str_replace('_', '.', 'astrada_subscription').'.repository.product', $productRepository);
        $container->setAlias(str_replace('_', '.', 'astrada_subscription').'.repository.subscription', $subscriptionRepository);
    }
}
