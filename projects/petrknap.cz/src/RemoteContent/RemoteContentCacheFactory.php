<?php

namespace PetrKnapCz\RemoteContent;

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
        $config = $container->get(CONFIG);

        return new FilesystemAdapter(
            $config[CONFIG_CACHE_REMOTE_CONTENT_NAMESPACE],
            $config[CONFIG_CACHE_REMOTE_CONTENT_LIFETIME],
            $config[CONFIG_CACHE_DIR]
        );
    }
}
