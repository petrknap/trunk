<?php

namespace Storyboard\Test;

use Storyboard\StoryboardGenerator;

class StoryboardGeneratorTest extends TestCase
{
    private function getGenerator()
    {
        return new StoryboardGenerator(
            __DIR__ . '/data/source_dir',
            __DIR__ . '/data/target_dir'
        );
    }

    public function testGeneratesHtmlOutput()
    {
        $this->getGenerator()->generate();

        $this->assertFileEquals(
            __DIR__ . '/data/target_dir/index.html.expect',
            __DIR__ . '/data/target_dir/index.html'
        );
    }
}
