<?php

namespace PetrKnap\Php\ServiceManager\Exception;

use Psr\Container\NotFoundExceptionInterface;

class ServiceNotFoundException extends ServiceManagerException implements NotFoundExceptionInterface
{
    // Service not found
}
