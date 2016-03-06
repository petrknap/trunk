<?php

namespace PetrKnap\Test\Php\ServiceManager\ServiceManagerTest;

use PetrKnap\Php\ServiceManager\ConfigBuilder;
use PetrKnap\Php\ServiceManager\FactoryInterface;
use PetrKnap\Php\ServiceManager\ServiceLocatorInterface;

class DependentServiceFactory implements FactoryInterface
{
    public static function getConfig()
    {
        $config = new ConfigBuilder();
        $config->addInvokable("IndependentService", IndependentService::getClass());
        $config->addFactory("DependentService", __CLASS__);

        return $config->getConfig();
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var IndependentService $independentService */
        $independentService = $serviceLocator->get("IndependentService");

        return new DependentService($independentService);
    }
}
