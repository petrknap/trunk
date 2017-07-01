<?php

namespace PetrKnap\Symfony\MarkdownWeb\DependencyInjection;

use const PetrKnap\Symfony\MarkdownWeb\CONFIG;
use const PetrKnap\Symfony\MarkdownWeb\CONTROLLER_CACHE;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;
use const PetrKnap\Symfony\MarkdownWeb\TWIG_EXTENSION;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $crawlerDefinition = $container->getDefinition(CONFIG);
        $crawlerDefinition->setArguments([$config]);

        $crawlerDefinition = $container->getDefinition(CRAWLER_SERVICE);
        $crawlerDefinition->setArguments([
            $config['directory'],
        ]);

        $twigExtensionDefinition = $container->getDefinition(TWIG_EXTENSION);
        $twigExtensionDefinition->addMethodCall('setSite', [$config['site']]);

        $container->setDefinition(CONTROLLER_CACHE, new Definition(
            FilesystemAdapter::class,
            [
                CONTROLLER_CACHE,
                $config['cache']['max_age'],
                $container->getParameter('kernel.cache_dir')
            ]
        ));
    }
}
