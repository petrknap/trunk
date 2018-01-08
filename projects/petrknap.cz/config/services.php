<?php

use PetrKnapCz\Api\BackUpService;
use PetrKnapCz\Api\BackUpServiceFactory;
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
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

$cb = new ConfigurationBuilder();
$cb->setSharedByDefault(true);

$cb->addService(CONFIG, ${CONFIG});
$cb->addService(Request::class, Request::createFromGlobals());
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
$cb->addFactory(Analytics::class, function (ContainerInterface $container) {
    $config = $container->get(CONFIG);
    /** @var Request $request */
    $request = $container->get(Request::class);

    if ($request->cookies->has('ga_client_id')) {
        $clientId = $request->cookies->get('ga_client_id');
    } else {
        $clientId = Uuid::uuid4()->toString();
        $response = new Response();
        $response->headers->setCookie(new Cookie('ga_client_id', $clientId, new DateTime('+2 years')));
        $response->sendHeaders();
    }

    $analytics = (new Analytics(true))
        ->setAsyncRequest(true)
        ->setProtocolVersion('1') // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
        ->setTrackingId($config[CONFIG_GA_TRACKING_ID])
        ->setDataSource('web')
        ->setClientId($clientId)
        ->setIpOverride($request->getClientIp())
        ->setUserLanguage($request->getPreferredLanguage())
        ->setHitType('pageview')
        ->setDocumentHostName($request->getHost())
        ->setDocumentPath($request->getRequestUri());

    if ($request->headers->has('User-Agent')) {
        $analytics->setUserAgentOverride($request->headers->get('User-Agent'));
    }

    if ($request->headers->has('Referer')) {
        $analytics->setUserAgentOverride($request->headers->get('Referer'));
    }

    return $analytics;
});
$cb->setShared(Analytics::class, false);
$cb->addFactory(RemoteContentAccessor::class, RemoteContentAccessorFactory::class);
$cb->addFactory(RemoteContentCache::class, RemoteContentCacheFactory::class);
$cb->addFactory(UrlShortenerService::class, UrlShortenerServiceFactory::class);
$cb->addFactory(BackUpService::class, BackUpServiceFactory::class);

ServiceManager::setConfig($cb->getConfig());
