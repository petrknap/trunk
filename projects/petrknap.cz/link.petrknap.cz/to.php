<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\Response;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

require_once __DIR__ . '/../vendor/autoload.php';

if (isset($_GET['keyword']) && is_string($_GET['keyword'])) {
    container()
        ->get(Analytics::class)
        ->sendPageView();

    container()
        ->get(UrlShortenerService::class)
        ->getResponse($_GET['keyword'])
        ->send();
} else {
    (new Response(null, Response::HTTP_FORBIDDEN))->send();
}
