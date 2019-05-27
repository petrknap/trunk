<?php

namespace Ucetnictvi\Invoice;

use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Entity\Contact;
use Ucetnictvi\Entity\Invoice;
use Ucetnictvi\Entity\InvoiceItem;

class Loader
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param string $inputDirectory
     * @return Invoice[]
     */
    public function getAllInvoices(string $inputDirectory): array
    {
        $contacts = $this->getAllContacts($inputDirectory);
        $invoicesData = $this->serializer->decode(
            file_get_contents($inputDirectory . DIRECTORY_SEPARATOR . 'invoices.csv'),
            'csv'
        );

        $groupedInvoicesData = [];
        $invoiceId = null;
        foreach ($invoicesData as $invoiceData)
        {
            $invoiceData= $this->trimKeys($invoiceData);
            if ($invoiceData['id']) {
                $invoiceId = $invoiceData['id'];
                $invoiceData['items'] = [];
                $invoiceData['seller'] = $contacts[$invoiceData['seller']];
                $invoiceData['buyer'] = $contacts[$invoiceData['buyer']];
                $groupedInvoicesData[$invoiceId] = $invoiceData;
            }
            $groupedInvoicesData[$invoiceId]['items'][] = InvoiceItem::create($invoiceData);
        }

        return array_map(Invoice::class . '::create', $groupedInvoicesData);
    }

    /**
     * @param string $inputDirectory
     * @return Contact[]
     */
    public function getAllContacts(string $inputDirectory):array
    {
        $contactsData = $this->serializer->decode(
            file_get_contents($inputDirectory . DIRECTORY_SEPARATOR . 'contacts.csv'),
            'csv'
        );

        $contacts = [];
        foreach ($contactsData as $contactData)
        {
            $contactData = $this->trimKeys($contactData);
            $contact = Contact::create($contactData);
            $contacts[$contact->getId()] = $contact;
        }

        return $contacts;
    }

    private function trimKeys(array $data): array
    {
        $trimmed = [];
        foreach ($data as $key => $value)
        {
            $trimmed[preg_replace('/[[:^print:]]/', '', $key)] = $value;
        }
        return $trimmed;
    }
}
