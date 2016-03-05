<?php

namespace PetrKnap\Php\ServiceManager;

use Interop\Container\ContainerInterface;
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
        // TODO add has instance check - exception
        // TODO add non-empty config check - trigger_error(..., E_USER_NOTICE);
        self::$config = $config;
    }

    /**
     * Sets (appends) configuration
     *
     * @param array $config
     */
    public static function addConfig(array $config)
    {
        // TODO add has instance check - exception
        // TODO add override check - trigger_error(..., E_USER_WARNING);
        self::$config = array_replace_recursive(self::$config, $config);
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
