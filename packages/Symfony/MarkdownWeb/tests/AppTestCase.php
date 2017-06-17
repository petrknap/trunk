<?php

use Netpromotion\SymfonyUp\UpTestCase;

class AppTestCase extends UpTestCase
{
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
