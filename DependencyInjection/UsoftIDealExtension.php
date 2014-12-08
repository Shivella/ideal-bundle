<?php

namespace Usoft\IDealBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UsoftIDealExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config['providers'] as $name => $serviceConfig) {

            if ($name == 'easy_ideal') {
                $container->setDefinition('easy_ideal', new DefinitionDecorator('easy_ideal_driver'))->replaceArgument(0, $serviceConfig);
            }

            if ($name == 'mollie') {
                $container->setDefinition('mollie', new DefinitionDecorator('mollie_driver'))->replaceArgument(0, $serviceConfig);
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
