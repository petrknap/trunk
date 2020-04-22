<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\RedirectResponse;

require_once __DIR__ . '/../vendor/autoload.php';

$targetGenerator = function ($uri) {
    return "https://petrknap.github.io{$uri}";
};

$uri = $_SERVER['REQUEST_URI'];
switch ($uri) {
    case '/':
        $uri = '/index_cz.html';
        break;
    case '/sitemap.xml':
        header("Content-Type: text/xml");
        break;
    case '/robots.txt':
        header("Content-Type: text/plain");
        break;
    default:
        (new RedirectResponse($targetGenerator($uri), RedirectResponse::HTTP_FOUND))->send();
        exit;
}

container()
    ->get(RemoteContentAccessor::class)
    ->getResponse($targetGenerator($uri))
    ->send();
