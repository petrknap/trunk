<?php

namespace PetrKnap\Php\ServiceManager\Test\ServiceManagerTest;

use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\FactoryInterface;
use PetrKnap\Php\ServiceManager\ServiceLocatorInterface;

class DependentServiceFactory implements FactoryInterface
{
    public static function getConfig()
    {
        $config = new ConfigurationBuilder();
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
