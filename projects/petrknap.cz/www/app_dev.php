<?php

use Netpromotion\SymfonyUp\SymfonyUp;

require_once __DIR__ . '/../app/autoload.php';

SymfonyUp::createFromKernelClass(WwwKernel::class)->runWeb('dev', true);
