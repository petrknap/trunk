<?php

namespace PetrKnap\Nette\Bootstrap;

use Nette\DI\Container;

abstract class Bootstrap
{
    const OPTION_IS_TEST_RUN = "testRun";

    /**
     * @var array
     */
    private static $options;

    /**
     * @internal
     */
    public function __construct()
    {
        user_error(sprintf("Called internal constructor of %s.", get_called_class()), E_USER_WARNING);
    }

    /**
     * @return array
     */
    protected static function getOptions()
    {
        return self::$options;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected static function getOption($name)
    {
        return isset(self::$options[$name]) ? self::$options[$name] : null;
    }

    /**
     * @return bool
     */
    abstract protected function getDebugMode();

    /**
     * @return string
     */
    abstract protected function getAppDir();

    /**
     * @return string
     */
    abstract protected function getLogDir();

    /**
     * @return string
     */
    abstract protected function getTempDir();

    /**
     * @return string[]
     */
    abstract protected function getConfigFiles();

    protected static function getConfigurator()
    {
        $me = @new static();
        $configurator = new Configurator(array("appDir" => $me->getAppDir()));

        if (class_exists("Tracy\\Debugger")) {
            $configurator->setDebugMode($me->getDebugMode());
            if (!self::getOption(self::OPTION_IS_TEST_RUN)) {
                $configurator->enableDebugger($me->getLogDir());
            }
        }
        $configurator->setTempDirectory($me->getTempDir());

        foreach ($me->getConfigFiles() as $configFile) {
            $configurator->addConfig($configFile);
        }

        return $configurator;
    }

    /**
     * @param array $options
     * @return Container
     */
    public static function getContainer(array $options = array())
    {
        self::$options = $options;
        return static::getConfigurator()->createContainer();
    }
}
