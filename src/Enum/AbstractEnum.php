<?php

namespace PetrKnap\Php\Enum;

/**
 * Abstract enum object
 *
 * @author  Petr Knap <dev@petrknap.cz>
 * @since   2016-01-23
 * @package PetrKnap\Php\Enum
 * @version 0.1
 * @license https://github.com/petrknap/php-enum/blob/master/LICENSE MIT
 */
abstract class AbstractEnum
{
    /**
     * @var self[][]
     */
    private static $instances;

    /**
     * @var mixed[]
     */
    private $items = [];

    /**
     * @var mixed
     */
    private $key;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $key
     * @throws EnumException
     */
    public function __construct($key)
    {
        $this->key = $key;
        $this->value = $this->get($key);
    }

    /**
     * Creates magical factories for easier access to enum
     *
     * @param mixed $key enum key
     * @param array $args ignored
     * @return mixed
     */
    public static function __callStatic($key, array $args)
    {
        $className = get_called_class();

        $instances = &self::$instances[$className];

        if (!is_array($instances)) {
            $instances = [];
        }

        $instance = &$instances[$key];

        if (!($instance instanceof $className)) {
            $instance = new $className($key);
        }

        return $instance;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed[] $items
     */
    protected function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @param mixed $key
     * @return bool
     */
    private function exists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @param mixed $key
     * @return mixed
     * @throws EnumException
     */
    private function get($key)
    {
        if (!$this->exists($key)) {
            throw new EnumException(
                sprintf(
                    "%s does not exists in %s",
                    $key,
                    get_called_class()
                ),
                EnumException::OUT_OF_RANGE
            );
        }

        return $this->items[$key];
    }
}
