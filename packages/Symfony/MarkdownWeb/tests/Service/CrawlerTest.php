<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test\Service;

use PetrKnap\Symfony\MarkdownWeb\Model\Index;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use PetrKnap\Symfony\MarkdownWeb\Test\MarkdownWebTestCase;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;

class CrawlerTest extends MarkdownWebTestCase
{
    private function getPageDir()
    {
        return (new \SplFileInfo(__DIR__ . "/../../src/Resources/demo"))->getRealPath();
    }

    /**
     * @return Crawler|object
     */
    private function getCrawler()
    {
        return $this->getKernel()->getContainer()->get(CRAWLER_SERVICE);
    }

    public function testServiceIsRegistered()
    {
        $this->assertInstanceOf(Crawler::class, $this->getCrawler());
    }

    public function testAcceptsOnlySupportedFiles()
    {
        $files = $this->invoke([$this->getCrawler(), "getFiles"], [$this->getPageDir()]);
        sort($files);

        $this->assertEquals([
            "{$this->getPageDir()}/index.md",
            "{$this->getPageDir()}/libero/ante-molestie-porttitor.md",
            "{$this->getPageDir()}/libero/index.md",
            "{$this->getPageDir()}/libero/orci-varius-natoque-penatibus-et-magnis.md",
            "{$this->getPageDir()}/libero/vivamus-accumsan-libero.md",
            "{$this->getPageDir()}/sitemap.md",
            "{$this->getPageDir()}/vestibulum-ullamcorper.md",
        ], $files);
    }

    public function testBuildsIndex()
    {
        $this->assertInstanceOf(Index::class, $this->getCrawler()->getIndex(function ($url) {
            return $url;
        }));
    }
}
