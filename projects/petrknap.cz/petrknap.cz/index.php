<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$request = Request::createFromGlobals();

$response = container()
    ->get(RemoteContentAccessor::class)
    ->getResponse('https://petrknap.github.io/index_cz.html');

$response = container()
    ->get(RavealAsPages::class)
    ->modifyResponse($request, $response);

/** @var Response $response */
$response->send();
