<?php

use AppBundle\AppBundle;
use Netpromotion\SymfonyUp\AppKernelTrait;
use Netpromotion\SymfonyUp\UpKernel;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

class AppKernel extends UpKernel
{
    use AppKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new AppBundle(),
        ];
    }

    public function getProjectDir()
    {
        return __DIR__ . '/..';
    }
}
