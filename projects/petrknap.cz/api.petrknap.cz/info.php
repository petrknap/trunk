<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpParamsInspection */
authorize(container()->get(Request::class));

phpinfo();
