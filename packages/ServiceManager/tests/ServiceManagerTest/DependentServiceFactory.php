<?php

namespace PetrKnap\Php\ServiceManager\Test\ServiceManagerTest;

use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;

class DependentServiceFactory implements FactoryInterface
{
    public static function getConfig()
    {
        $config = new ConfigurationBuilder();
        $config->addInvokable("IndependentService", IndependentService::getClass());
        $config->addFactory("DependentService", __CLASS__);

        return $config->getConfig();
    }

    public function createService(ContainerInterface $container)
    {
        /** @var IndependentService $independentService */
        $independentService = $container->get("IndependentService");

        return new DependentService($independentService);
    }
}
