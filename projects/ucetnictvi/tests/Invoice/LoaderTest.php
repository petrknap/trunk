<?php

namespace Ucetnictvi\Test\Invoice;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;
use Ucetnictvi\Entity\Contact;
use Ucetnictvi\Entity\Invoice;
use Ucetnictvi\Entity\InvoiceItem;
use Ucetnictvi\Invoice\Loader;

class LoaderTest extends TestCase
{
    public function testLoadsAllInvoices()
    {
        $expectedInvoices = [
            2019001 => Invoice::create([
                'id' => 2019001,
                'seller' => Contact::create([
                    'id' => 'me',
                ]),
                'buyer' => Contact::create([
                    'id' => 'company1',
                ]),
                'subject' => 'The first invoice',
                'issueDate' => '2019-05-24',
                'dueDate' => '2019-06-07',
                'items' => [
                    InvoiceItem::create([
                        'type' => 'Service',
                        'description' => 'Creation of the first invoice',
                        'quantity' => 1,
                        'unit' => 'pcs',
                        'unitPrice' => 79.50,
                    ]),
                ],
                'currency' => 'CZK',
            ]),
            2019002 => Invoice::create([
                'id' => 2019002,
                'seller' => Contact::create([
                    'id' => 'me',
                ]),
                'buyer' => Contact::create([
                    'id' => 'company2',
                ]),
                'subject' => 'The second invoice',
                'issueDate' => '2019-05-24',
                'dueDate' => '2019-06-07',
                'items' => [
                    InvoiceItem::create([
                        'type' => 'Service',
                        'description' => 'Creation of the second invoice',
                        'quantity' => 1,
                        'unit' => 'pcs',
                        'unitPrice' => 79.50,
                    ]),
                    InvoiceItem::create([
                        'type' => 'Service',
                        'description' => 'Consultation',
                        'quantity' => 40,
                        'unit' => 'h',
                        'unitPrice' => 600,
                    ]),
                ],
                'currency' => 'CZK',
            ]),
        ];
        $this->assertEquals(
            $expectedInvoices,
            (new Loader(new Serializer([],[new CsvEncoder()])))
                ->getAllInvoices(__DIR__ . '/LoaderTest')
        );
    }

    public function testLoadsAllContacts()
    {
        $expectedInvoices = [
            'me' => Contact::create([
                'id' => 'me',
            ]),
            'company1' => Contact::create([
                'id' => 'company1',
            ]),
            'company2' => Contact::create([
                'id' => 'company2',
            ]),
        ];
        $this->assertEquals(
            $expectedInvoices,
            (new Loader(new Serializer([],[new CsvEncoder()])))
                ->getAllContacts(__DIR__ . '/LoaderTest')
        );
    }
}
