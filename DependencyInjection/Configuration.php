<?php

namespace Usoft\IDealBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ideal');

        $rootNode->children()
                    ->arrayNode('mollie')->canBeUnset()->children()
                        ->scalarNode('key')->isRequired()->end()
                        ->scalarNode('description')->defaultValue('mollie ideal payment')->end()
                        ->booleanNode('test_mode')->defaultTrue()->end()
                    ->end()
                ->end();

        return $treeBuilder;
    }
}
