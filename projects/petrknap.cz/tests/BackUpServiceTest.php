<?php

namespace PetrKnapCz\Test;

use PetrKnapCz\BackUpService;

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
            [__FILE__, '~/_var_www_html_projects_petrknap.cz_tests_BackUpServiceTest.php'],
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
        $varBackupDir = __DIR__ . '/../var/BackUpServiceTest_testBackUpWorks';
        exec(sprintf(
            'rsync --delete --recursive %s %s',
            escapeshellarg(__DIR__ . '/BackUpServiceTest/backup/'),
            escapeshellarg($varBackupDir)
        ));
        $backUpService = new BackUpService($varBackupDir, [
            __DIR__ . '/BackUpServiceTest/unchanged.txt',
            __DIR__ . '/BackUpServiceTest/changed.txt',
            __DIR__ . '/BackUpServiceTest/new.txt',
            __DIR__ . '/BackUpServiceTest/directory/unchanged.txt',
            __DIR__ . '/BackUpServiceTest/directory/changed.txt',
            __DIR__ . '/BackUpServiceTest/directory/new.txt',
        ]);

        $backUpService->backUp();

        $dataSet = [
            #region unchanged files
            [
                's' => __DIR__ . '/BackUpServiceTest/unchanged.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/unchanged.txt')
            ],
            [
                's' => null,
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/unchanged.txt') . '.prev'
            ],
            [
                's' => __DIR__ . '/BackUpServiceTest/directory/unchanged.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/directory/unchanged.txt')
            ],
            [
                's' => null,
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/directory/unchanged.txt') . '.prev'
            ],
            #endregion
            #region changed files
            [
                's' => __DIR__ . '/BackUpServiceTest/changed.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/changed.txt')
            ],
            [
                's' => __DIR__ . '/BackUpServiceTest/backup/_var_www_html_projects_petrknap.cz_tests_BackUpServiceTest_changed.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/changed.txt') . '.prev'
            ],
            [
                's' => __DIR__ . '/BackUpServiceTest/directory/changed.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/directory/changed.txt')
            ],
            [
                's' => __DIR__ . '/BackUpServiceTest/backup/_var_www_html_projects_petrknap.cz_tests_BackUpServiceTest_directory_changed.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/directory/changed.txt') . '.prev'
            ],
            #endregion
            #region new files
            [
                's' => __DIR__ . '/BackUpServiceTest/new.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/new.txt')
            ],
            [
                's' => null,
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/new.txt') . '.prev'
            ],
            [
                's' => __DIR__ . '/BackUpServiceTest/directory/new.txt',
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/directory/new.txt')
            ],
            [
                's' => null,
                'd' => $backUpService->getBackUpPath(__DIR__ . '/BackUpServiceTest/directory/new.txt') . '.prev'
            ],
            #endregion
        ];
        foreach ($dataSet as $data) {
            if ($data['s']) {
                $this->assertFileEquals($data['s'], $data['d']);
            } else {
                $this->assertFileNotExists($data['d']);
            }
        }
    }
}
