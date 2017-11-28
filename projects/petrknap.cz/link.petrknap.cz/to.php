<?php

use PetrKnap\Php\ServiceManager\ServiceManager;
use PetrKnapCz\UrlShortener\UrlShortenerService;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

require_once __DIR__ . '/../vendor/autoload.php';

ServiceManager::getInstance()
    ->get(Analytics::class)
    ->sendPageView();

ServiceManager::getInstance()
    ->get(UrlShortenerService::class)
    ->getResponse($_GET['keyword'])
    ->send();
