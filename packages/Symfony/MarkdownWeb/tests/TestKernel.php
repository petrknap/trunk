<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use Netpromotion\SymfonyUp\UpKernel;
use PetrKnap\Symfony\MarkdownWeb\MarkdownWebBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

class TestKernel extends UpKernel
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
