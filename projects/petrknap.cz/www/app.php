<?php

use Netpromotion\SymfonyUp\SymfonyUp;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../app/autoload.php';

Request::enableHttpMethodParameterOverride(); // remove this line if kernel.http_method_override = false

SymfonyUp::createFromKernelClass(WwwKernel::class)->runWeb();
