<?php

use Netpromotion\SymfonyUp\UpKernel;
use PetrKnap\Symfony\MarkdownWeb\MarkdownWebBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

class AppKernel extends UpKernel
{
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new MonologBundle(),
            new SensioFrameworkExtraBundle(),
            new TwigBundle(),
            new MarkdownWebBundle(),
        ];

        if ('dev' === $this->getEnvironment()) {
            $bundles[] = new DebugBundle();
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function getProjectDir()
    {
        return __DIR__ . '/..';
    }

    public function getRootDir()
    {
        return $this->getProjectDir() . '/app';
    }

    public function getCacheDir()
    {
        return $this->getProjectDir() . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return $this->getProjectDir() . '/var/logs';
    }
}
