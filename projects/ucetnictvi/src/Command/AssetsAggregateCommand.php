<?php

namespace Ucetnictvi\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Ucetnictvi\Asset\Generator;
use Ucetnictvi\Asset\Loader;

class AssetsAggregateCommand extends Command
{
    private $kernel;
    private $loader;
    private $generator;

    public function __construct(
        KernelInterface $kernel,
        Loader $loader,
        Generator $generator
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->generator = $generator;
        $this->loader = $loader;
    }

    protected function configure()
    {
        $this->setName('assets:aggregate')
            ->setDescription('Aggregates CSVs')
            ->addArgument('data.csv', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataFiles = $input->getArgument('data.csv');
        $io = new SymfonyStyle($input, $output);

        $io->comment(sprintf(
            'Aggregating assets for the <info>%s</info> environment with debug <info>%s</info>',
            $this->kernel->getEnvironment(),
            var_export($this->kernel->isDebug(), true)
        ));

        $this->generator->generateXlsx(
            $this->loader->getAllAssetOperations($dataFiles),
            "{$dataFiles[0]}.xlsx"
        );

        $io->success(sprintf(
            'Assets for the "%s" environment (debug=%s) was successfully aggregated.',
            $this->kernel->getEnvironment(),
            var_export($this->kernel->isDebug(), true)
        ));

        return /* void */;
    }
}
