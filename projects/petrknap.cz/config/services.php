<?php

use PetrKnapCz\RemoteContent\RemoteContentAccessor;
use PetrKnapCz\RemoteContent\RemoteContentAccessorFactory;
use PetrKnapCz\RemoteContent\RemoteContentCache;
use PetrKnapCz\RemoteContent\RemoteContentCacheFactory;
use PetrKnapCz\UrlShortener\UrlShortenerService;
use PetrKnapCz\UrlShortener\UrlShortenerServiceFactory;
use PetrKnap\Php\MigrationTool\SqlMigrationTool;
use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\ServiceManager;
use Psr\Container\ContainerInterface;

$cb = new ConfigurationBuilder();
$cb->setSharedByDefault(true);

$cb->addService(CONFIG, ${CONFIG});
$cb->addFactory(\PDO::class, function (ContainerInterface $container) {
    $config = $container->get(CONFIG);

    $pdo = new \PDO(
        $config[CONFIG_DB_DSN],
        $config[CONFIG_DB_USER],
        $config[CONFIG_DB_PASSWORD]
    );

    return $pdo;
});
$cb->addFactory(SqlMigrationTool::class, function (ContainerInterface $container) {
    $config = $container->get(CONFIG);

    return new SqlMigrationTool(
        $config[CONFIG_DB_MIGRATIONS_DIR],
        $container->get(\PDO::class)
    );
});
$cb->addFactory(RemoteContentAccessor::class, RemoteContentAccessorFactory::class);
$cb->addFactory(RemoteContentCache::class, RemoteContentCacheFactory::class);
$cb->addFactory(UrlShortenerService::class, UrlShortenerServiceFactory::class);

ServiceManager::setConfig($cb->getConfig());
