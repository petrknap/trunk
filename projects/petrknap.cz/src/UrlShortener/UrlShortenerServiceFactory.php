<?php

namespace App\UrlShortener;

use PetrKnap\Php\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

class UrlShortenerServiceFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ContainerInterface $container)
    {
        return new UrlShortenerService($container->get(\PDO::class));
    }
}
