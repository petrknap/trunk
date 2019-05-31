<?php

namespace Ucetnictvi\Invoice;

use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use PetrKnap\Php\SpaydQr\SpaydQr;
use Ucetnictvi\Entity\Invoice;

class Generator
{
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generatePdf(Invoice $invoice, string $path, string $locale, string $subjectType)
    {
        $oldLocale = \Locale::getDefault();
        \Locale::setDefault($locale);
        $htmlInvoice = $this->twig->render("pdf/invoice/{$locale}/{$subjectType}.html.twig", [
            'invoice' => $invoice,
            'qr_code' => SpaydQr::create(
                $invoice->getSeller()->getIban(),
                (new DecimalMoneyParser(new ISOCurrencies()))->parse((string) $invoice->getTotalPrice(), $invoice->getCurrency())
            )->setVariableSymbol($invoice->getId())
        ]);
        \Locale::setDefault($oldLocale);

        $this->convertHtmlStringToPdfFile($htmlInvoice, $path);
    }

    private function convertHtmlStringToPdfFile(string $html, string $path)
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $pdf = new Mpdf([
            'fontDir' => array_merge($fontDirs, [
                __DIR__ . '/../../templates/fonts',
            ]),
            'fontdata' => array_merge($fontData, [
                'scrgunny' => [
                    'R' => 'scrgunny.ttf',
                ],
            ])
        ]);
        $pdf->WriteHTML($html);
        $pdf->Output($path, Destination::FILE);
    }
}
