<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test\Model;

use PetrKnap\Symfony\MarkdownWeb\Model\Index;
use PetrKnap\Symfony\MarkdownWeb\Model\Page;
use PetrKnap\Symfony\MarkdownWeb\Test\TestCase;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;

class IndexTest extends TestCase
{
    const ROOT_DIRECTORY = __DIR__ . '/../../src/Resources/demo';

    /**
     * @return Index
     */
    private function getIndex()
    {
        return $this->getKernel()->getContainer()->get(CRAWLER_SERVICE)->getIndex(function ($url) {
            return $url;
        });
    }

    /**
     * @dataProvider dataCanProcessFiles
     * @param string $rootDirectory
     * @param array $pathsToFiles
     * @param mixed $expected
     */
    public function testCanProcessFiles($rootDirectory, array $pathsToFiles, $expected)
    {
        $this->assertEquals(
            $expected,
            Index::fromFiles(
                $rootDirectory,
                $pathsToFiles,
                function ($url) {
                    return $url;
                }
            )
        );
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
        $urlModifier = function ($url) {
            return $url;
        };

        return [
            [
                [], null, null, 'url:asc', [
                    '/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md', $urlModifier),
                    '/libero/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/index.md', $urlModifier),
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md', $urlModifier),
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md', $urlModifier),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md', $urlModifier),
                    '/sitemap.xml' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/sitemap.md', $urlModifier),
                    '/vestibulum-ullamcorper.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md', $urlModifier),
                ],
            ],
            [
                [], null, null, 'url:desc', [
                    '/vestibulum-ullamcorper.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md', $urlModifier),
                    '/sitemap.xml' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/sitemap.md', $urlModifier),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md', $urlModifier),
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md', $urlModifier),
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md', $urlModifier),
                    '/libero/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/index.md', $urlModifier),
                    '/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md', $urlModifier),
                ],
            ],
            [
                ['layout' => 'article.html', 'tags' => 'lacus'], null, null, 'url:asc', [
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md', $urlModifier),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md', $urlModifier),
                ],
            ],
            [
                ['layout' => 'article.html', '!tags' => 'lacus'], null, null, 'url:asc', [
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md', $urlModifier),
                ],
            ],
            [
                ['tags' => 'lacus', 'layout' => 'web.html'], null, null, 'url:asc', [
                ],
            ],
            [
                ['tags' => ['lacus', 'quis']], null, null, 'url:asc', [
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md', $urlModifier),
                ],
            ],
            [
                ['layout' => 'web.html'], null, null, 'url:asc', [
                    '/' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md', $urlModifier),
                    '/vestibulum-ullamcorper.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md', $urlModifier),
                ],
            ],
            [
                ['layout' => 'article.html'], 1, 2, 'date:desc', [
                    '/libero/orci-varius-natoque-penatibus-et-magnis.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md', $urlModifier),
                    '/libero/vivamus-accumsan-libero.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md', $urlModifier),
                ],
            ],
            [
                ['layout' => 'article.html'], 2, 2, 'date:desc', [
                    '/libero/ante-molestie-porttitor.html' => Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md', $urlModifier),
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
        $urlModifier = function ($url) {
            return $url;
        };

        return [
            ['/', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/index.md', $urlModifier)],
            ['/vestibulum-ullamcorper.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/vestibulum-ullamcorper.md', $urlModifier)],
            ['/libero/', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/index.md', $urlModifier)],
            ['/libero/ante-molestie-porttitor.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/ante-molestie-porttitor.md', $urlModifier)],
            ['/libero/orci-varius-natoque-penatibus-et-magnis.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/orci-varius-natoque-penatibus-et-magnis.md', $urlModifier)],
            ['/libero/vivamus-accumsan-libero.html', Page::fromFile(self::ROOT_DIRECTORY, self::ROOT_DIRECTORY . '/libero/vivamus-accumsan-libero.md', $urlModifier)],
        ];
    }
}
