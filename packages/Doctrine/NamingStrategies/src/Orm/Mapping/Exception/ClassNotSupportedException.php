<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\NamingStrategies\Orm\Mapping\Exception;

use Doctrine\ORM\ORMException;

class ClassNotSupportedException extends ORMException
{
    public static function create(string $className, string $reason): self
    {
        return new self("{$className} is not supported, {$reason}");
    }
}
