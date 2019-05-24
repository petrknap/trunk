<?php

namespace Ucetnictvi\Invoice;

use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Entity\Invoice;
use Ucetnictvi\Entity\InvoiceItem;

class Loader
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function getAllInvoices(string $inputDirectory): array
    {
        $invoicesData = $this->serializer->decode(
            file_get_contents($inputDirectory . DIRECTORY_SEPARATOR . 'invoices.csv'),
            'csv'
        );
        $groupedInvoicesData = [];
        $invoiceId = null;
        foreach ($invoicesData as $invoiceData)
        {
            if ($invoiceData['id']) {
                $invoiceId = $invoiceData['id'];
                $invoiceData['items'] = [];
                $groupedInvoicesData[$invoiceId] = $invoiceData;
            }
            $groupedInvoicesData[$invoiceId]['items'][] = InvoiceItem::create($invoiceData);
        }

        return array_map(Invoice::class . '::create', array_values($groupedInvoicesData));
    }
}
