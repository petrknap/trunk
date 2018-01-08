<?php

namespace PetrKnapCz\Test\Api;

use PetrKnapCz\Api\BackUpService;
use PetrKnapCz\Test\TestCase;

class BackUpServiceTest extends TestCase
{
    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            BackUpService::class,
            $this->get(BackUpService::class)
        );
    }

    /**
     * @dataProvider dataReturnsCorrectBackUpPath
     * @param string $sourcePath
     * @param string $expected
     */
    public function testReturnsCorrectBackUpPath($sourcePath, $expected)
    {
        $this->assertEquals($expected, (new BackUpService('~', []))->getBackUpPath($sourcePath));
    }

    public function dataReturnsCorrectBackUpPath()
    {
        return [
            [__FILE__, '~/_var_www_html_projects_petrknap.cz_tests_Api_BackUpServiceTest.php'],
        ];
    }

    public function testReturnsCorrectChangedFiles()
    {
        $backUpService = new BackUpService(__DIR__ . '/BackUpServiceTest/backup', [
            __DIR__ . '/BackUpServiceTest/unchanged.txt',
            __DIR__ . '/BackUpServiceTest/changed.txt',
            __DIR__ . '/BackUpServiceTest/new.txt',
            __DIR__ . '/BackUpServiceTest/directory/unchanged.txt',
            __DIR__ . '/BackUpServiceTest/directory/changed.txt',
            __DIR__ . '/BackUpServiceTest/directory/new.txt',
        ]);

        $this->assertEquals([
            __DIR__ . '/BackUpServiceTest/changed.txt',
            __DIR__ . '/BackUpServiceTest/new.txt',
            __DIR__ . '/BackUpServiceTest/directory/changed.txt',
            __DIR__ . '/BackUpServiceTest/directory/new.txt',
        ], iterator_to_array($backUpService->getChangedFiles()));
    }

    public function testBackUpWorks()
    {
        $this->markTestIncomplete();
    }
}
