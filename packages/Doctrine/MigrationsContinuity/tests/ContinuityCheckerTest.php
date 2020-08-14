<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\MigrationsContinuity\Tests;

use Doctrine\Migrations\Exception\AbortMigration;
use PetrKnap\Doctrine\MigrationsContinuity\ContinuityChecker;
use PHPUnit\Framework\TestCase;
use stdClass;

class ContinuityCheckerTest extends TestCase
{
    /**
     * @dataProvider dataDoesNotThrowWhenMigrationsAreContinuous
     * @noinspection PhpDocSignatureInspection
     */
    public function testDoesNotThrowWhenMigrationsAreContinuous(array $migrations): void
    {
        (new ContinuityChecker())->check($this->getMigrations($migrations));

        $this->assertTrue(true);
    }

    private function getMigrations(array $versionsToMigratedMap): array
    {
        $migrations = [];
        foreach ($versionsToMigratedMap as $version => $migrated) {
            $migration = $this->getMockBuilder(stdClass::class)->setMethods([
                'getVersion',
                'isMigrated',
            ])->getMock();
            $migration->method('getVersion')->willReturn($version);
            $migration->method('isMigrated')->willReturn($migrated);
            $migrations[] = $migration;
        }

        return $migrations;
    }

    public function dataDoesNotThrowWhenMigrationsAreContinuous(): array
    {
        return [
            [
                [
                    '20020714075330' => false,
                ],
            ],
            [
                [
                    '20020714075330' => true,
                ],
            ],
            [
                [
                    '20020714075330' => false,
                    '20020714075331' => false,
                ],
            ],
            [
                [
                    '20020714075330' => true,
                    '20020714075331' => false,
                ],
            ],
            [
                [
                    '20020714075330' => true,
                    '20020714075331' => true,
                ],
            ],
        ];

    }

    public function testThrowsWhenMigrationsAreNotContinuous(): void
    {
        $this->expectException(AbortMigration::class);

        (new ContinuityChecker())->check($this->getMigrations([
            '20020714075330' => false,
            '20020714075331' => true,
        ]));
    }
}
