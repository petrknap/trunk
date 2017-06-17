<?php

use Netpromotion\SymfonyUp\AppKernelTrait;
use Netpromotion\SymfonyUp\UpKernel;
use PetrKnap\Symfony\MarkdownWeb\MarkdownWebBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

class AppKernel extends UpKernel
{
    use AppKernelTrait;

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new SensioFrameworkExtraBundle(),
            new MarkdownWebBundle(),
        ];
    }

    public function getProjectDir()
    {
        return __DIR__ . '/..';
    }
}
