<?php

namespace PetrKnap\Test\Php\ServiceManager\ServiceManagerTest;

use PetrKnap\Php\ServiceManager\ConfigBuilder;
use PetrKnap\Php\ServiceManager\Exception\ServiceLocatorException;
use PetrKnap\Php\ServiceManager\FactoryInterface;
use PetrKnap\Php\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class DependentServiceFactory implements FactoryInterface
{
    public static function getConfig()
    {
        $config = new ConfigBuilder();
        $config->addInvokable("IndependentService", IndependentService::getClass());
        $config->addFactory("DependentService", __CLASS__);

        return $config->getConfig();
    }

    /**
     * Create an object
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @throws ServiceNotCreatedException error while creating the service
     * @throws ServiceLocatorException if any other error occurs
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var IndependentService $independentService */
        $independentService = $serviceLocator->get("IndependentService");

        return new DependentService($independentService);
    }
}