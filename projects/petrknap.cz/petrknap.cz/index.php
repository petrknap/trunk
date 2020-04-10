<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

container()
    ->get(RemoteContentAccessor::class)
    ->getResponse('https://petrknap.github.io/index_cz.html')
    ->send();
