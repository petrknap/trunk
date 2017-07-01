<?php

namespace PetrKnap\Symfony\MarkdownWeb\DependencyInjection;

use PetrKnap\Symfony\MarkdownWeb\MarkdownWebTwigExtension;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use const PetrKnap\Symfony\MarkdownWeb\CONFIG;
use const PetrKnap\Symfony\MarkdownWeb\CONTROLLER_CACHE;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;
use const PetrKnap\Symfony\MarkdownWeb\TWIG_EXTENSION;

class MarkdownWebExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new MarkdownWebConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setDefinition(CONFIG, new Definition(MarkdownWebConfiguration::class))
            ->setArguments([
                $config,
            ]);

        $container->setDefinition(CRAWLER_SERVICE, new Definition(Crawler::class))
            ->setArguments([
                $config['directory'],
            ]);

        $container->setDefinition(TWIG_EXTENSION, new Definition(MarkdownWebTwigExtension::class))
            ->setAutowired(true)
            ->addMethodCall('setSite', [$config['site']])
            ->addTag('twig.extension');

        $container->setDefinition(CONTROLLER_CACHE, new Definition(FilesystemAdapter::class))
            ->setArguments([
                CONTROLLER_CACHE,
                $config['cache']['max_age'],
                $container->getParameter('kernel.cache_dir')
            ]);
    }
}
