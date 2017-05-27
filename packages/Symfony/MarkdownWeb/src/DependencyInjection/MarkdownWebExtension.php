<?php

namespace PetrKnap\Symfony\MarkdownWeb\DependencyInjection;

use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_ALIAS;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MarkdownWebExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new MarkdownWebConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('config.yml');

        $crawlerDefinition = $container->getDefinition(BUNDLE_ALIAS . '.config');
        $crawlerDefinition->setArguments([$config]);

        $crawlerDefinition = $container->getDefinition(BUNDLE_ALIAS . '.crawler');
        $crawlerDefinition->setArguments([
            $config['directory'],
        ]);

        $twigExtensionDefinition = $container->getDefinition(BUNDLE_ALIAS . '.twig');
        $twigExtensionDefinition->addMethodCall('setSite', [$config['site']]);

        $this->addClassesToCompile([
            // TODO
        ]);
    }
}
