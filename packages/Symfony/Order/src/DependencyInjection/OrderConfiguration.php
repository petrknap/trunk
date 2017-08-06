<?php

namespace PetrKnap\Symfony\Order\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class OrderConfiguration extends \ArrayObject implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('order');

        $rootNode->children()
            ->scalarNode('provider')
            ->isRequired()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
