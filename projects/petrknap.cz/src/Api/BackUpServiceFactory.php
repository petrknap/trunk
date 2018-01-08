<?php

namespace PetrKnapCz\Api;

use PetrKnap\Php\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class BackUpServiceFactory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createService(ContainerInterface $container)
    {
        $config = $container->get(CONFIG);

        return new BackUpService(
            $config[CONFIG_BACK_UP_DIR],
            $config[CONFIG_BACKED_UP_FILES]
        );
    }
}
