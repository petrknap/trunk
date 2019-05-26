<?php

namespace Ucetnictvi\Invoice;

use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;
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
                $invoice->seller->iban,
                (new DecimalMoneyParser(new ISOCurrencies()))->parse((string) $invoice->getTotalPrice(), $invoice->currency)
            )->setVariableSymbol($invoice->id)
        ]);
        \Locale::setDefault($oldLocale);

        $pdf = new Mpdf();
        $pdf->WriteHTML($htmlInvoice);
        $pdf->Output($path, Destination::FILE);
    }
}
