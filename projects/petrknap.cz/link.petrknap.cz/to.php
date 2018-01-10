<?php

use PetrKnap\Php\ServiceManager\ServiceManager;
use PetrKnapCz\UrlShortener\UrlShortenerService;
use Symfony\Component\HttpFoundation\Response;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

require_once __DIR__ . '/../vendor/autoload.php';

if (isset($_GET['keyword']) && is_string($_GET['keyword'])) {
    PetrKnapCz\container()
        ->get(Analytics::class)
        ->sendPageView();

    PetrKnapCz\container()
        ->get(UrlShortenerService::class)
        ->getResponse($_GET['keyword'])
        ->send();
} else {
    (new Response(null, Response::HTTP_FORBIDDEN))->send();
}
