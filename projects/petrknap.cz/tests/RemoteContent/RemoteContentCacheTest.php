<?php

namespace Test\AppBundle\Service;

use App\RemoteContent\RemoteContentAccessor;
use App\RemoteContent\RemoteContentCache;
use App\Test\AppTestCase;
use Psr\Cache\CacheItemInterface;

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
