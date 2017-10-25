<?php

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../vendor/netpromotion/symfony-up/src/autoload.php';

$loader->addClassMap([
    AppKernel::class => __DIR__ . '/AppKernel.php',
    AppTestCase::class => __DIR__ . '/../tests/AppTestCase.php',
    WwwKernel::class => __DIR__ . '/WwwKernel.php',
]);

return $loader;
