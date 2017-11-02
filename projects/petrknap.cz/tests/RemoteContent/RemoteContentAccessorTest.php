<?php

namespace App\Test\RemoteContent;

use App\RemoteContent\RemoteContentAccessor;
use App\Test\AppTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

class RemoteContentAccessorTest extends AppTestCase
{
    /**
     * @return CacheItemPoolInterface
     */
    private function getForeverEmptyCache()
    {
        $itemValue = null;
        $item = $this->getMockBuilder(CacheItemInterface::class)->getMock();
        $item->method('isHit')->willReturn(false);
        $item->method('set')->willReturnCallback(function ($value) use (&$itemValue) {
            $itemValue = $value;
        });
        $item->method('get')->willReturnCallback(function () use (&$itemValue) {
            return $itemValue;
        });
        $cache = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $cache->method('getItem')->willReturn($item);

        /** @var CacheItemPoolInterface $cache */
        return $cache;
    }

    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            RemoteContentAccessor::class,
            $this->get(RemoteContentAccessor::class)
        );
    }

    /**
     * @group slow
     * @dataProvider dataGetsRemoteContent
     * @param string $uri
     * @param mixed $expected
     */
    public function testGetsRemoteContent($uri, $expected)
    {
        $rca = new RemoteContentAccessor($this->getForeverEmptyCache());

        $this->assertEquals($expected, $rca->getRemoteContent($uri));
    }

    public function dataGetsRemoteContent()
    {
        return [
            [
                'https://httpbin.org/robots.txt',
                Response::create(
                    "User-agent: *\nDisallow: /deny\n",
                    Response::HTTP_OK,
                    [
                        'Content-Type' => 'text/plain'
                    ]
                ),
            ],
            [
                'https://httpbin.org/redirect-to?url=https%3A%2F%2Fhttpbin.org%2Frobots.txt',
                Response::create(
                    "User-agent: *\nDisallow: /deny\n",
                    Response::HTTP_OK,
                    [
                        'Content-Type' => 'text/plain'
                    ]
                ),
            ],
            [
                'https://httpbin.org/status/403',
                Response::create(
                    null,
                    Response::HTTP_FORBIDDEN,
                    [
                        'Content-Type' => 'text/html; charset=utf-8'
                    ]
                ),
            ],
            [
                'https://httpbin.org/status/404',
                Response::create(
                    null,
                    Response::HTTP_NOT_FOUND,
                    [
                        'Content-Type' => 'text/html; charset=utf-8'
                    ]
                ),
            ],
            [
                'https://httpbin.org/status/500',
                Response::create(
                    null,
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    [
                        'Content-Type' => 'text/html; charset=utf-8'
                    ]
                ),
            ],
        ];
    }
}
