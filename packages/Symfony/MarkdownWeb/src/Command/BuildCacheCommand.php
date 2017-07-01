<?php

namespace PetrKnap\Symfony\MarkdownWeb\Command;

use PetrKnap\Symfony\MarkdownWeb\Controller\DefaultController;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use const PetrKnap\Symfony\MarkdownWeb\BUILD_CACHE_COMMAND;
use const PetrKnap\Symfony\MarkdownWeb\CONFIG;
use const PetrKnap\Symfony\MarkdownWeb\CONTROLLER_CACHE;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;

class BuildCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName(BUILD_CACHE_COMMAND)
            ->setDescription("Creates cache for all known URLs on website for better user experience.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $config = $this->getContainer()->get(CONFIG);
        $crawler = $this->getContainer()->get(CRAWLER_SERVICE);
        $controller = new DefaultController();
        $controller->setContainer($this->getContainer());
        $cache = $this->getContainer()->get(CONTROLLER_CACHE);
        $io = new SymfonyStyle($input, $output);

        $io->comment(sprintf(
            'Caching pages for the <info>%s</info> environment with debug <info>%s</info>',
            $kernel->getEnvironment(),
            var_export($kernel->isDebug(), true)
        ));

        if (!$config['cache']['enabled']) {
            $io->warning(sprintf(
                'Page caching is disabled in configuration.'
            ));

            return /* void */;
        }

        $cache->clear();

        /** @var Crawler $crawler */
        $urls = array_keys($crawler->getIndex([$controller, 'urlModifier'])->getPages([]));
        $io->progressStart(count($urls));
        foreach ($urls as $url) {
            $kernel->handle(Request::create($url));
            $io->progressAdvance(1);
            if ($output->isVerbose()) {
                $io->comment(sprintf(
                    '<info>%s</info> cached',
                    $url
                ));
            }
        }
        $io->progressFinish();

        $io->success(sprintf(
            'Pages for the "%s" environment (debug=%s) was successfully cached.',
            $kernel->getEnvironment(),
            var_export($kernel->isDebug(), true)
        ));

        return /* void */;
    }
}
