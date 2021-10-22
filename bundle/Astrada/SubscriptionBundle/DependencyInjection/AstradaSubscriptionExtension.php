<?php


namespace Astrada\SubscriptionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AstradaSubscriptionExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $container->setParameter('astrada_subscription.config', $config);
        $container->setParameter('astrada_subscription.config.subscription.class', $config['subscription_class']);
        $container->setParameter('astrada_subscription.config.subscription.repository', $config['subscription_repository']);
        $container->setParameter('astrada_subscription.config.product.repository', $config['product_repository']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('strategy.product.yml');
        $loader->load('strategy.subscription.yml');
    }

    public function getAlias()
    {
        return 'astrada_subscription';
    }
}