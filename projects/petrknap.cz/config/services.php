<?php

use App\RemoteContent\RemoteContentAccessor;
use App\RemoteContent\RemoteContentAccessorFactory;
use App\RemoteContent\RemoteContentCache;
use App\RemoteContent\RemoteContentCacheFactory;
use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\ServiceManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cb = new ConfigurationBuilder();

$cb->addFactory(RemoteContentAccessor::class, RemoteContentAccessorFactory::class);
$cb->setShared(RemoteContentAccessor::class, true);

$cb->addFactory(RemoteContentCache::class, RemoteContentCacheFactory::class);
$cb->setShared(RemoteContentCache::class, true);

ServiceManager::setConfig($cb->getConfig());
