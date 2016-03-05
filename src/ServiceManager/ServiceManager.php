<?php

namespace PetrKnap\Php\ServiceManager;

use Interop\Container\ContainerInterface;
use LogicException;
use PetrKnap\Php\Singleton\SingletonInterface;
use PetrKnap\Php\Singleton\SingletonTrait;
use Zend\ServiceManager\ServiceManager as RealServiceManager;

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
class ServiceManager implements ContainerInterface, SingletonInterface
{
    use SingletonTrait;

    /**
     * @var array
     */
    private static $config = [];

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

    /**
     * @var RealServiceManager
     */
    private $serviceManager;

    protected function __construct()
    {
        $this->serviceManager = new RealServiceManager(self::$config);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        return $this->serviceManager->get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function has($name)
    {
        return $this->serviceManager->has($name);
    }
}
