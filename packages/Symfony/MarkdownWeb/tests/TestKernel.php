<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use Netpromotion\SymfonyUp\AppKernel;
use PetrKnap\Symfony\MarkdownWeb\MarkdownWebBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

class TestKernel extends AppKernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new MarkdownWebBundle(),
        ];
    }

    public function getRootDir()
    {
        return __DIR__ . '/../app';
    }
}
