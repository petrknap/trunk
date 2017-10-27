<?php

namespace App\RemoteContent;

use PetrKnap\Php\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

class RemoteContentAccessorFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ContainerInterface $container)
    {
        return new RemoteContentAccessor($container->get(RemoteContentCache::class));
    }
}
