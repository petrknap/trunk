<?php

namespace Storyboard\Test;

use Storyboard\LogFileProcessor;

class LogFileProcessorTest extends TestCase
{
    public function testWorksWhenFileIsValid()
    {
        $this->assertSame(
            file_get_contents(__DIR__ . '/data/valid.log.expect'),
            (new LogFileProcessor())->processFile(__DIR__ . '/data/valid.log')
        );
    }

    public function testThrowsWhenFileIsInvalid()
    {
        $this->expectException(\RuntimeException::class);
        (new LogFileProcessor())->processFile(__DIR__ . '/data/invalid.log');
    }
}
