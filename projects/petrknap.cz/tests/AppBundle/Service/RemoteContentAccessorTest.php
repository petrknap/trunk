<?php

namespace Test\AppBundle\Service;

use AppBundle\Service\RemoteContentAccessor;

class RemoteContentAccessorTest extends \AppTestCase
{
    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            RemoteContentAccessor::class,
            $this->getContainer()->get(RemoteContentAccessor::class)
        );
    }

    /**
     * @dataProvider dataGetsRemoteContent
     * @param string $uri
     * @param mixed $expected
     */
    public function testGetsRemoteContent($uri, $expected)
    {
        $rca = new RemoteContentAccessor();

        $this->assertEquals($expected, $rca->getRemoteContent($uri));
    }

    public function dataGetsRemoteContent()
    {
        return [
            [
                'https://httpbin.org/robots.txt',
                "User-agent: *\nDisallow: /deny\n",
            ],
            [
                'https://httpbin.org/redirect-to?url=https%3A%2F%2Fhttpbin.org%2Frobots.txt',
                "User-agent: *\nDisallow: /deny\n",
            ],
            [
                'https://httpbin.org/status/403',
                null,
            ],
            [
                'https://httpbin.org/status/404',
                null,
            ],
            [
                'https://httpbin.org/status/500',
                null,
            ],
        ];
    }
}
