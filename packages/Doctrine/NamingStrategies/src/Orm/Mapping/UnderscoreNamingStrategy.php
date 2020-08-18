<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\NamingStrategies\Orm\Mapping;

use PetrKnap\Doctrine\NamingStrategies\Orm\Mapping\Exception\ClassNotSupportedException;

class UnderscoreNamingStrategy extends \Doctrine\ORM\Mapping\UnderscoreNamingStrategy
{
    private $prefix;
    private $allowedRootClasses;

    public function __construct($case = CASE_LOWER, bool $numberAware = false, string $prefix = null, array $allowedRootClasses = [])
    {
        parent::__construct($case, $numberAware);

        $this->prefix = $prefix;
        $this->allowedRootClasses = $allowedRootClasses;
    }

    /**
     * @inheritDoc
     */
    public function classToTableName($className)
    {
        if ($this->prefix) {
            $prefixWithSlash = "{$this->prefix}\\";
            if (strpos($className, $prefixWithSlash) !== 0) {
                if (strpos($className, '\\') === false && in_array($className, $this->allowedRootClasses)) {
                    return parent::classToTableName($className);
                } else {
                    throw ClassNotSupportedException::create($className, "missing prefix {$this->prefix}");
                }
            }
            $className = substr($className, strlen($prefixWithSlash));
        }

        $partialNames = explode('\\', $className);

        return implode('__', array_map(function (string $partialName) {
            return parent::classToTableName($partialName);
        }, $partialNames));
    }
}
