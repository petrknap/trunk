<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$uri = $_GET['uri'] ?? '';
switch ($uri) {
    case '':
        $uri = 'index_cz.html';
        break;
    case 'sitemap.xml':
        header("Content-Type: text/xml");
        break;
    case 'robots.txt':
        header("Content-Type: text/plain");
        break;
    default:
        http_response_code(Response::HTTP_NOT_FOUND);
        header("Content-Type: text/plain");
        die('Page Not Found');
}

container()
    ->get(RemoteContentAccessor::class)
    ->getResponse("https://petrknap.github.io/{$uri}")
    ->send();
