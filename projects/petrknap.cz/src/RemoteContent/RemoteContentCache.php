<?php

use PetrKnap\Php\ServiceManager\FactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class_alias(FilesystemAdapter::class,'App\RemoteContent\RemoteContentCache');
