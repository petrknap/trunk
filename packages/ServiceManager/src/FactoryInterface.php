<?php

namespace PetrKnap\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\Exception\ServiceManagerException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotCreatedException;
use Psr\Container\ContainerInterface;

/**
 * Factory interface
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-03-05
 * @category Patterns
 * @package  PetrKnap\Php\ServiceManager
 * @license  https://github.com/petrknap/php-servicemanager/blob/master/LICENSE MIT
 */
interface FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $serviceLocator
     * @throws ServiceNotCreatedException error while creating the service
     * @throws ServiceManagerException if any other error occurs
     * @return object
     */
    public function createService(ContainerInterface $serviceLocator);
}
