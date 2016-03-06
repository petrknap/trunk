<?php

namespace PetrKnap\Php\ServiceManager;

/**
 * Config builder
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-03-05
 * @category Patterns
 * @package  PetrKnap\Php\ServiceManager
 * @license  https://github.com/petrknap/php-servicemanager/blob/master/LICENSE MIT
 */
class ConfigurationBuilder
{
    use ConfigurationCheckerTrait;

    const
        SERVICES = "services",
        INVOKABLES = "invokables",
        FACTORIES = "factories",
        SHARED = "shared",
        SHARED_BY_DEFAULT = "shared_by_default";

    private $config = [
        self::SERVICES => [/* service name => service instance pairs */],
        self::INVOKABLES => [/* service name => class name pairs for classes that do not have required constructor arguments */],
        self::FACTORIES => [/* service name => factory pairs; factories may be any callable, string name resolving to an invokable class, or string name resolving to a FactoryInterface instance */],
        self::SHARED => [/* service name => flag pairs; the flag is a boolean indicating */],
        self::SHARED_BY_DEFAULT => false // boolean, indicating if services in this instance should be shared by default
    ];

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $serviceName
     * @param mixed $serviceInstance
     * @return $this
     */
    public function addService($serviceName, $serviceInstance)
    {
        $this->config[self::SERVICES][$serviceName] = $serviceInstance;

        return $this;
    }

    /**
     * @param string $serviceName
     * @param string $className
     * @return $this
     */
    public function addInvokable($serviceName, $className)
    {
        $this->checkInvokable($serviceName, $className);
        $this->config[self::INVOKABLES][$serviceName] = $className;

        return $this;
    }

    /**
     * @param string $serviceName
     * @param string|callable $factory
     * @return $this
     */
    public function addFactory($serviceName, $factory)
    {
        $this->checkFactory($serviceName, $factory);
        $this->config[self::FACTORIES][$serviceName] = $factory;

        return $this;
    }

    /**
     * @param string $serviceName
     * @param bool $isShared
     * @return $this
     */
    public function setShared($serviceName, $isShared)
    {
        $this->checkShared($serviceName, $isShared);
        $this->config[self::SHARED][$serviceName] = $isShared;

        return $this;
    }

    /**
     * @param bool $isShared
     * @return $this
     */
    public function setSharedByDefault($isShared)
    {
        $this->checkSharedByDefault($isShared);
        $this->config[self::SHARED_BY_DEFAULT] = $isShared;

        return $this;
    }
}
