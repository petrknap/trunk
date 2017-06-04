<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test\Model;

use Mni\FrontYAML\Parser;
use PetrKnap\Symfony\MarkdownWeb\Model\Page;
use PetrKnap\Symfony\MarkdownWeb\Test\TestCase;

class PageTest extends TestCase
{
    /**
     * @dataProvider dataCanProcessFile
     * @param string $rootDirectory
     * @param string $pathToFile
     * @param Page|null $expected
     */
    public function testCanProcessFile($rootDirectory, $pathToFile, $expected)
    {
        $this->assertEquals($expected, Page::fromFile($rootDirectory, $pathToFile));
    }

    public function dataCanProcessFile()
    {
        $rootDirectory = __DIR__ . "/../../src/Resources/demo";
        $document = (new Parser())->parse(file_get_contents($rootDirectory . "/index.md"));

        return [
            [$rootDirectory, $rootDirectory . "/index.md", new Page(
                array_merge($document->getYAML(), ["url" => "/"]),
                $document->getContent()
            )],
            [$rootDirectory, "not found", null]
        ];
    }
}
