<?php

namespace App\RemoteContent;

use PetrKnap\Php\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RemoteContentCacheFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ContainerInterface $container)
    {
        return new FilesystemAdapter(
            CACHE_REMOTE_CONTENT_NAMESPACE,
            CACHE_REMOTE_CONTENT_LIFETIME,
            CACHE_DIR
        );
    }
}
