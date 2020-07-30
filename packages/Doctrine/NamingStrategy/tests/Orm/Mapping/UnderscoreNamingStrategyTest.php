<?php

namespace PetrKnap\Doctrine\NamingStrategy\Tests\Orm\Mapping;

use PetrKnap\Doctrine\NamingStrategy\Orm\Mapping\Exception\ClassNotSupportedException;
use PetrKnap\Doctrine\NamingStrategy\Orm\Mapping\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;

class UnderscoreNamingStrategyTest extends TestCase
{
    /**
     * @dataProvider dataClassToTableName
     * @noinspection PhpDocSignatureInspection
     */
    public function testClassToTableName(?string $prefix, string $className, string $tableName): void
    {
        $this->assertSame(
            $tableName,
            (new UnderscoreNamingStrategy(CASE_LOWER, true, $prefix))->classToTableName($className)
        );
    }

    public function dataClassToTableName(): array
    {
        return [
            [null, 'App\\Entity\\Foo\\Bar', 'app__entity__foo__bar'],
            ['App\\Entity', 'App\\Entity\\Foo\\Bar', 'foo__bar'],
        ];
    }

    public function testClassToTableThrowsWhenClassNameIsNotSupported(): void
    {
        $this->expectException(ClassNotSupportedException::class);

        (new UnderscoreNamingStrategy(CASE_LOWER, true, 'Foo'))->classToTableName('Bar');
    }
}