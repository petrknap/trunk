<?php

namespace PetrKnap\Php\ServiceManager;

use Exception;
use LogicException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotCreatedException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotFoundException;
use PetrKnap\Php\ServiceManager\Exception\UnsupportedFactoryException;
use PetrKnap\Php\Singleton\SingletonInterface;
use PetrKnap\Php\Singleton\SingletonTrait;

/**
 * Service manager
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-03-05
 * @category Patterns
 * @package  PetrKnap\Php\ServiceManager
 * @version  0.1
 * @license  https://github.com/petrknap/php-servicemanager/blob/master/LICENSE MIT
 */
class ServiceManager implements ServiceLocatorInterface, SingletonInterface
{
    use SingletonTrait;

    /**
     * @var array
     */
    private static $config = [];

    private $services = [];
    private $invokables = [];
    private $factories = [];
    private $shared = [];
    private $sharedByDefault = false;

    /**
     * Sets (overrides) configuration
     *
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        if (self::hasInstance()) {
            throw new LogicException("Can not change the configuration, instance already exists.");
        }

        if (func_num_args() == 1 && !empty(self::$config)) {
            trigger_error("Current configuration will be overridden by new one.", E_USER_NOTICE);
        }

        self::$config = $config;
    }

    /**
     * Sets (appends) configuration
     *
     * @param array $config
     */
    public static function addConfig(array $config)
    {
        foreach ($config as $type => $services) {
            if (is_array($services)) {
                foreach ($services as $name => $value) {
                    if (isset(self::$config[$type][$value])) {
                        trigger_error("Current {$name} will be overridden by new one.", E_USER_WARNING);
                    }
                }
            }
        }

        self::setConfig(array_replace_recursive(self::$config, $config), true);
    }

    /**
     * Returns current config
     *
     * @return array
     */
    public static function getConfig()
    {
        return self::$config;
    }

    protected function __construct()
    {
        if (isset(self::$config[ConfigBuilder::SERVICES])) {
            $this->services = self::$config[ConfigBuilder::SERVICES];
        }
        if (isset(self::$config[ConfigBuilder::INVOKABLES])) {
            $this->invokables = self::$config[ConfigBuilder::INVOKABLES];
        }
        if (isset(self::$config[ConfigBuilder::FACTORIES])) {
            $this->factories = self::$config[ConfigBuilder::FACTORIES];
        }
        if (isset(self::$config[ConfigBuilder::SHARED])) {
            $this->shared = self::$config[ConfigBuilder::SHARED];
        }
        if (isset(self::$config[ConfigBuilder::SHARED_BY_DEFAULT])) {
            $this->sharedByDefault = self::$config[ConfigBuilder::SHARED_BY_DEFAULT];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get($serviceName)
    {
        $service = $this->iterateThroughServices(function ($data) use ($serviceName) {
            if ($data["service_name"] == $serviceName) {
                try {
                    if (isset($data["instance"])) {
                        return $data["instance"];
                    }

                    if (isset($data["class_name"])) {
                        return new $data["class_name"];
                    }

                    if (isset($data["factory"])) {
                        $factory = $data["factory"];
                        if (is_callable($factory)) {
                            return call_user_func($factory, $this);
                        }
                        if (class_exists($factory)) {
                            $factory = new $factory;
                            if ($factory instanceof FactoryInterface) {
                                return $factory->createService($this);
                            }
                        }
                        throw new UnsupportedFactoryException(sprintf("Unsupported factory for service `%s`", $serviceName));
                    }
                } catch (Exception $e) {
                    throw new ServiceNotCreatedException(sprintf("Service `%s` not created", $serviceName), 0, $e);
                }
            }
            return null;
        }, null);

        if ($service === null) {
            throw new ServiceNotFoundException(sprintf("Service `%s` not found", $serviceName));
        }

        if ($this->isShared($serviceName) && !isset($this->services[$serviceName])) {
            $this->services[$serviceName] = $service;
        }

        return $service;
    }

    /**
     * {@inheritDoc}
     */
    public function has($serviceName)
    {
        return $this->iterateThroughServices(function ($data) use ($serviceName) {
            if ($data["service_name"] == $serviceName) {
                return true;
            }
            return null;
        }, false);
    }

    private function isShared($serviceName)
    {
        if (isset($this->shared[$serviceName])) {
            return $this->shared[$serviceName];
        }

        return $this->sharedByDefault;
    }

    /**
     * Iterates through services and calls callback
     *
     * @param callable $callback
     * @param mixed $defaultOutput
     * @return mixed
     */
    private function iterateThroughServices(callable $callback, $defaultOutput)
    {
        foreach ($this->services as $serviceName => $instance) {
            $output = call_user_func($callback, ["service_name" => $serviceName, "instance" => $instance]);
            if ($output) {
                return $output;
            }
        }

        foreach ($this->invokables as $serviceName => $className) {
            $output = call_user_func($callback, ["service_name" => $serviceName, "class_name" => $className]);
            if ($output) {
                return $output;
            }
        }

        foreach ($this->factories as $serviceName => $factory) {
            $output = call_user_func($callback, ["service_name" => $serviceName, "factory" => $factory]);
            if ($output) {
                return $output;
            }
        }

        return $defaultOutput;
    }
}
