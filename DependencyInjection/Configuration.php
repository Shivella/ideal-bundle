<?php
/*
* (c) Wessel Strengholt <wessel.strengholt@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Usoft\IDealBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Wessel Strengholt <wessel.strengholt@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('usoft_i_deal');

        $rootNode->children()
            ->arrayNode('mollie')->isRequired()->children()
                ->scalarNode('key')->isRequired()->end()
                ->scalarNode('description')->defaultValue('mollie ideal payment')->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
