<?php

namespace Ucetnictvi\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Ucetnictvi\Invoice\Generator;
use Ucetnictvi\Invoice\Loader;

class InvoicesCreateCommand extends Command
{
    private $kernel;

    private $loader;

    private $generator;

    private $inputDirectory;

    private $outputDirectory;

    private $locale;

    private $subjectType;

    public function __construct(
        KernelInterface $kernel,
        Loader $loader,
        Generator $generator,
        string $inputDirectory,
        string $outputDirectory,
        string $locale,
        string $subjectType
    ) {
        parent::__construct();
        $this->kernel = $kernel;
        $this->generator = $generator;
        $this->loader = $loader;
        $this->inputDirectory = $inputDirectory;
        $this->outputDirectory = $outputDirectory;
        $this->locale = $locale;
        $this->subjectType = $subjectType;
    }

    protected function configure()
    {
        $this->setName('invoices:create')
            ->setDescription('Creates new invoices');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment(sprintf(
            'Creating invoices for the <info>%s</info> environment with debug <info>%s</info>',
            $this->kernel->getEnvironment(),
            var_export($this->kernel->isDebug(), true)
        ));

        $invoices = $this->loader->getAllInvoices($this->inputDirectory);
        $io->progressStart(count($invoices));
        foreach ($invoices as $invoice) {
            $path = $this->outputDirectory . DIRECTORY_SEPARATOR . $invoice->id . '.pdf';
            $this->generator->generatePdf($invoice, $path, $this->locale, $this->subjectType);
            $io->progressAdvance(1);
            if ($output->isVerbose()) {
                $io->comment(sprintf(
                    '<info>%s</info> created',
                    $path
                ));
            }
        }
        $io->progressFinish();

        $io->success(sprintf(
            'Invoices for the "%s" environment (debug=%s) was successfully created.',
            $this->kernel->getEnvironment(),
            var_export($this->kernel->isDebug(), true)
        ));

        return /* void */;
    }
}
