<?php

namespace PetrKnapCz\UrlShortener;

use PetrKnap\Php\ServiceManager\FactoryInterface;
use PetrKnapCz\RemoteContent\RemoteContentAccessor;
use Psr\Container\ContainerInterface;

class UrlShortenerServiceFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ContainerInterface $container)
    {
        return new UrlShortenerService($container->get(\PDO::class), $container->get(RemoteContentAccessor::class));
    }
}
