<?php

namespace PetrKnap\Symfony\Order\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_ALIAS;

class OrderConfiguration extends \ArrayObject implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('order');

        $rootNode->children()
            ->arrayNode('cookie')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('name')
            ->defaultValue('order')
            ->end()
            ->scalarNode('expire_after')
            ->defaultValue(604800)
            ->end()
            ->end()
            ->end()
            ->scalarNode('item_provider')
            ->isRequired()
            ->end()
            ->scalarNode('customer_provider')
            ->isRequired()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
