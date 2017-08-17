<?php

use AppBundle\AppBundle;
use Netpromotion\SymfonyUp\AppKernelTrait;
use Netpromotion\SymfonyUp\UpKernel;
use PetrKnap\Symfony\MarkdownWeb\MarkdownWebBundle;
use PetrKnap\Symfony\Order\OrderBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;

class AppKernel extends UpKernel
{
    use AppKernelTrait;

    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new MonologBundle(),
            new SensioFrameworkExtraBundle(),
            new TwigBundle(),
            new SwiftmailerBundle(),
            new OrderBundle(),
            new MarkdownWebBundle(),
            new AppBundle(),
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
}
