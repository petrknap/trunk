<?php

namespace Test\AppBundle\Service;

use App\RemoteContent\RemoteContentCache;
use App\Test\AppTestCase;

class RemoteContentCacheTest extends AppTestCase
{
    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            RemoteContentCache::class,
            $this->get(RemoteContentCache::class)
        );
    }
}
