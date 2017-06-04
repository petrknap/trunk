<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test\Model;

use PetrKnap\Symfony\MarkdownWeb\Model\Index;
use PetrKnap\Symfony\MarkdownWeb\Model\Page;
use PetrKnap\Symfony\MarkdownWeb\Test\TestCase;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;

class IndexTest extends TestCase
{
    const ROOT_DIRECTORY = __DIR__ . '/../../src/Resources/demo';
    const PAGINATION_STEP = 2;

    /**
     * @return Index
     */
    private function getIndex()
    {
        return $this->getKernel()->getContainer()->get(CRAWLER_SERVICE)->getIndex();
    }

    /**
     * @dataProvider dataCanProcessFiles
     * @param string $rootDirectory
     * @param array $pathsToFiles
     * @param mixed $expected
     */
    public function testCanProcessFiles($rootDirectory, array $pathsToFiles, $expected)
    {
        $this->assertEquals($expected, Index::fromFiles($rootDirectory, $pathsToFiles, self::PAGINATION_STEP));
    }

    public function dataCanProcessFiles()
    {
        return []; // TODO
    }

    /**
     * @dataProvider dataReturnsPages
     * @param array $filters
     * @param int|null $pageNumber
     * @param int|null $paginationStep
     * @param $sortBy $orderBy
     * @param mixed $expected
     */
    public function testReturnsPages($filters, $pageNumber, $paginationStep, $sortBy, $expected)
    {
        $pages = $this->getIndex()->getPages($filters, $sortBy, $pageNumber, $paginationStep);

        $this->assertEquals($expected, $pages);
        $this->assertEquals(array_keys($expected), array_keys($pages));
        $this->assertEquals(array_values($expected), array_values($pages));
    }

    public function dataReturnsPages()
    {
        return [
            [
                [], null, null, 'url:asc', [
                    '/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md'),
                    '/libero/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/index.md'),
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md'),
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md'),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md'),
                    '/sitemap.xml' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/sitemap.md'),
                    '/vestibulum-ullamcorper.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md'),
                ],
            ],
            [
                [], null, null, 'url:desc', [
                    '/vestibulum-ullamcorper.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md'),
                    '/sitemap.xml' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/sitemap.md'),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md'),
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md'),
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md'),
                    '/libero/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/index.md'),
                    '/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md'),
                ],
            ],
            [
                ['layout' => 'article.html', 'tags' => 'lacus'], null, null, 'url:asc', [
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md'),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md'),
                ],
            ],
            [
                ['layout' => 'article.html', '!tags' => 'lacus'], null, null, 'url:asc', [
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md'),
                ],
            ],
            [
                ['tags' => 'lacus', 'layout' => 'web.html'], null, null, 'url:asc', [
                ],
            ],
            [
                ['tags' => ['lacus', 'quis']], null, null, 'url:asc', [
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md'),
                ],
            ],
            [
                ['layout' => 'web.html'], null, null, 'url:asc', [
                    '/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md'),
                    '/vestibulum-ullamcorper.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md'),
                ],
            ],
            [
                ['layout' => 'article.html'], 1, 2, 'date:desc', [
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md'),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md'),
            ],
            ],
            [
                ['layout' => 'article.html'], 2, 2, 'date:desc', [
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataReturnsPageForUrl
     * @param string $url
     * @param mixed $expected
     */
    public function testReturnsPageForUrl($url, $expected)
    {
        $this->assertEquals($expected, $this->getIndex()->getPage($url));
    }

    public function dataReturnsPageForUrl()
    {
        return [
            ['/', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md')],
            ['/vestibulum-ullamcorper.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md')],
            ['/libero/', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/index.md')],
            ['/libero/ante-molestie-porttitor.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md')],
            ['/libero/orci-varius-natoque-penatibus-et-magnis.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md')],
            ['/libero/vivamus-accumsan-libero.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md')],
        ];
    }
}
