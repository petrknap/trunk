<?php

namespace PetrKnap\Php\Enum;

/**
 * Abstract enum object
 *
 * @author  Petr Knap <dev@petrknap.cz>
 * @since   2016-01-23
 * @package PetrKnap\Php\Enum
 * @version 0.2
 * @license https://github.com/petrknap/php-enum/blob/master/LICENSE MIT
 */
abstract class AbstractEnum
{
    /**
     * @var self[][]
     */
    private static $instances;

    /**
     * @var mixed[][]
     */
    private static $constants = [];

    /**
     * @var mixed
     */
    private $constantName;

    /**
     * @var mixed
     */
    private $constantValue;

    /**
     * @param mixed $constantName
     * @throws EnumException
     */
    protected function __construct($constantName)
    {
        $this->constantName = $constantName;
        $this->constantValue = $this->get($constantName);
    }

    /**
     * Creates magical factories for easier access to enum
     *
     * @param mixed $constantName enum key
     * @param array $args ignored
     * @return mixed
     */
    public static function __callStatic($constantName, array $args)
    {
        $className = get_called_class();

        $instances = &self::$instances[$className];

        if (!is_array($instances)) {
            $instances = [];
        }

        $instance = &$instances[$constantName];

        if (!($instance instanceof $className)) {
            $instance = new $className($constantName);
        }

        return $instance;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->constantName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->constantValue;
    }

    /**
     * @param mixed[] $constants
     */
    protected static function setConstants(array $constants)
    {
        self::$constants[get_called_class()] = $constants;
    }

    /**
     * @param string $constantName
     * @return bool
     */
    private function exists($constantName)
    {
        return array_key_exists($constantName, self::$constants[get_called_class()]);
    }

    /**
     * @param string $constantName
     * @return mixed
     * @throws EnumException
     */
    private function get($constantName)
    {
        if (!$this->exists($constantName)) {
            throw new EnumException(
                sprintf(
                    "%s does not exist in %s",
                    $constantName,
                    get_called_class()
                ),
                EnumException::OUT_OF_RANGE
            );
        }

        return self::$constants[get_called_class()][$constantName];
    }
}
