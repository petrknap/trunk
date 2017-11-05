<?php

namespace Storyboard\Test;

use Storyboard\ImageFileProcessor;

class ImageFileProcessorTest extends TestCase
{
    /**
     * @dataProvider dataWorksWhenFileIsValid
     * @param string $ext
     */
    public function testWorksWhenFileIsValid($ext)
    {
        $this->assertSame(
            file_get_contents(__DIR__ . "/data/valid.{$ext}.expect"),
            (new ImageFileProcessor(__DIR__ . '/data/target_dir'))->processFile(__DIR__ . "/data/valid.{$ext}")
        );
    }

    public function dataWorksWhenFileIsValid()
    {
        return [['jpg'], ['png']];
    }

    public function testThrowsWhenFileIsInvalid()
    {
        $this->expectException(\RuntimeException::class);
        (new ImageFileProcessor(__DIR__ . '/data/target_dir'))->processFile(__DIR__ . '/data/invalid.png');
    }
}
