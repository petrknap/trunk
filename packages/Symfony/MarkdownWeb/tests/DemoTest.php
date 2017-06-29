<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use Symfony\Component\HttpFoundation\Request;

class DemoTest extends MarkdownWebTestCase
{
    /**
     * @dataProvider dataPageIsAccessible
     * @param string $url
     */
    public function testPageIsAccessible($url)
    {
        $response = $this->getKernel()->handle(Request::create($url));

        $this->assertTrue($response->isSuccessful());
    }

    public function dataPageIsAccessible()
    {
        return [
            ['/'],
            ['/sitemap.xml'],
            ['/libero/'],
            ['/libero/ante-molestie-porttitor.html'],
            ['/libero/orci-varius-natoque-penatibus-et-magnis.html'],
            ['/libero/vivamus-accumsan-libero.html'],
            ['/vestibulum-ullamcorper.html'],
        ];
    }
}
