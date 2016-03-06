<?php

namespace PetrKnap\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\Exception\ServiceLocatorException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotCreatedException;

interface FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @throws ServiceNotCreatedException error while creating the service
     * @throws ServiceLocatorException if any other error occurs
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator);
}
