<?php

use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class_alias(FilesystemAdapter::class,'App\RemoteContent\RemoteContentCache');
