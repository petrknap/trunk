<?php

namespace PetrKnapCz\RemoteContent;

use PetrKnapCz\TestCase;

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
