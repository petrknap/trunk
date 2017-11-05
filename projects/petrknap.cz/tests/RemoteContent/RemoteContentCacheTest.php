<?php

namespace PetrKnapCz\Test\RemoteContent;

use PetrKnapCz\RemoteContent\RemoteContentCache;
use PetrKnapCz\Test\TestCase;

class RemoteContentCacheTest extends TestCase
{
    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            RemoteContentCache::class,
            $this->get(RemoteContentCache::class)
        );
    }
}
