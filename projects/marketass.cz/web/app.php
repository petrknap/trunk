<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

define("DEBUG_MODE", true);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
if (DEBUG_MODE) {
    Debug::enable();
} else {
    include_once __DIR__.'/../var/bootstrap.php.cache';
}

$kernel = new AppKernel(DEBUG_MODE ? 'dev' : 'prod', DEBUG_MODE);
$kernel->loadClassCache();

if (DEBUG_MODE) {
    $kernel = new AppCache($kernel);
    Request::enableHttpMethodParameterOverride();
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
