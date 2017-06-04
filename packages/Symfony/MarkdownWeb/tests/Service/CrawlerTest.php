<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test\Service;

use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;
use PetrKnap\Symfony\MarkdownWeb\Model\Index;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use PetrKnap\Symfony\MarkdownWeb\Test\TestCase;

class CrawlerTest extends TestCase
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
        $this->assertEquals([
            "{$this->getPageDir()}/sitemap.md",
            "{$this->getPageDir()}/libero/orci-varius-natoque-penatibus-et-magnis.md",
            "{$this->getPageDir()}/libero/vivamus-accumsan-libero.md",
            "{$this->getPageDir()}/libero/ante-molestie-porttitor.md",
            "{$this->getPageDir()}/libero/index.md",
            "{$this->getPageDir()}/vestibulum-ullamcorper.md",
            "{$this->getPageDir()}/index.md",
        ], $this->invoke([$this->getCrawler(), "getFiles"], [$this->getPageDir()]));
    }

    public function testBuildsIndex()
    {
        $this->assertInstanceOf(Index::class, $this->getCrawler()->getIndex());
    }
}
