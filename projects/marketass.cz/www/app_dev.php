<?php

use Netpromotion\SymfonyUp\SymfonyUp;

require_once __DIR__ . '/../app/autoload.php';

SymfonyUp::createFromKernelClass(AppKernel::class)->runWeb('dev', true);
