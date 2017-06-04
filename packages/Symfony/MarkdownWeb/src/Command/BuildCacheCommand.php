<?php

namespace PetrKnap\Symfony\MarkdownWeb\Command;

use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_ALIAS;
use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_CONSOLE;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\HttpKernel\KernelInterface;

class BuildCacheCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName(BUNDLE_CONSOLE . ':build-cache')
            ->setDescription("Creates cache for all known URLs on website for better user experience.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var KernelInterface $kernel */
        $kernel = $this->getContainer()->get('kernel');
        $config = $this->getContainer()->get(BUNDLE_ALIAS . '.config');
        $crawler = $this->getContainer()->get(BUNDLE_ALIAS . '.crawler');
        $client = new Client($kernel);
        $io = new SymfonyStyle($input, $output);

        $io->comment(sprintf(
            'Caching pages for the <info>%s</info> environment with debug <info>%s</info>',
            $kernel->getEnvironment(),
            var_export($kernel->isDebug(), true)
        ));

        if (!$config['cached']) {
            $io->warning(sprintf(
                'Page caching is disabled in configuration.'
            ));

            return /* void */;
        }

        $this->getApplication()
            ->find('cache:clear')
            ->run(new ArrayInput(['command' => 'cache:clear']), new NullOutput());

        /** @var Crawler $crawler */
        $urls = array_keys($crawler->getIndex()->getPages([]));
        $io->progressStart(count($urls));
        foreach ($urls as $url) {
            $client->request('GET', $url);
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
