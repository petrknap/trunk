<?php

namespace PetrKnap\Symfony\MarkdownWeb\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_ALIAS;

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
            ->variableNode('site')
            ->defaultValue(['title' => 'Markdown Web Bundle for Symfony'])
            ->end();

        return $treeBuilder;
    }
}
