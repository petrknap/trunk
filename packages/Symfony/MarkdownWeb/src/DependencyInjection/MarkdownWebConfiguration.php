<?php

namespace PetrKnap\Symfony\MarkdownWeb\DependencyInjection;

use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_ALIAS;
use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_NAME;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class MarkdownWebConfiguration extends \ArrayObject implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(BUNDLE_ALIAS);

        $rootNode->children()
            ->scalarNode('directory')
            ->defaultValue(__DIR__ . '/../Resources/demo')
            ->end()
            ->booleanNode('cached')
            ->defaultValue(false)
            ->end()
            ->arrayNode('site')
            ->addDefaultsIfNotSet()
            ->children()
            ->scalarNode('title')
            ->defaultValue(BUNDLE_NAME)
            ->end()
            ->scalarNode('pagination_step')
            ->defaultValue(15)
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
