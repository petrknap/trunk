<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test\Model;

use Mni\FrontYAML\Parser;
use PetrKnap\Symfony\MarkdownWeb\Model\Page;
use PetrKnap\Symfony\MarkdownWeb\Test\MarkdownWebTestCase;

class PageTest extends MarkdownWebTestCase
{
    /**
     * @dataProvider dataCanProcessFile
     * @param string $rootDirectory
     * @param string $pathToFile
     * @param Page|null $expected
     */
    public function testCanProcessFile($rootDirectory, $pathToFile, $expected)
    {
        $uriModifier = function ($url) {
            return $url;
        };

        $this->assertEquals($expected, Page::fromFile($rootDirectory, $pathToFile, $uriModifier));
    }

    public function dataCanProcessFile()
    {
        $rootDirectory = __DIR__ . "/../../src/Resources/demo";
        $indexHtml = (new Parser())->parse(file_get_contents($rootDirectory . "/index.md"));
        $sitemapXml = (new Parser())->parse(file_get_contents($rootDirectory . "/sitemap.md"));

        return [
            [$rootDirectory, $rootDirectory . "/index.md", new Page(
                array_merge($indexHtml->getYAML(), ["url" => "/", "extension" => "html"]),
                $indexHtml->getContent()
            )],
            [$rootDirectory, $rootDirectory . "/sitemap.md", new Page(
                array_merge($sitemapXml->getYAML(), ["url" => "/sitemap.xml", "extension" => "xml"]),
                $sitemapXml->getContent()
            )],
            [$rootDirectory, "not found", null]
        ];
    }
}
