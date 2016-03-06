<?php

namespace PetrKnap\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\Exception\ServiceLocatorException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotFoundException;

interface ServiceLocatorInterface
{
    /**
     * Finds an service of the locator by its name and returns it.
     *
     * @param string $serviceName
     * @throws ServiceNotFoundException no service was found for this name
     * @throws ServiceLocatorException if any other error occurs
     * @return mixed
     */
    public function get($serviceName);

    /**
     * Returns true if the locator has service for the given name. Returns false otherwise.
     *
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName);
}