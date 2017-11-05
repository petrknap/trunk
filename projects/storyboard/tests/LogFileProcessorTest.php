<?php

namespace Storyboard\Test;

use Storyboard\LogFileProcessor;

class LogFileProcessorTest extends TestCase
{
    public function testCanProcessFile()
    {
        $this->assertSame(
            file_get_contents(__DIR__ . '/data/file.log.expect'),
            (new LogFileProcessor())->processFile(__DIR__ . '/data/file.log')
        );
    }
}
