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
    const INPUT_DIRECTORY = __DIR__ . '/LoaderTest';

    public function testLoadsAllInvoices()
    {
        $contacts = $this->getLoader()->getAllContacts(self::INPUT_DIRECTORY);
        $expectedInvoices = [
            2019001 => Invoice::create([
                'id' => 2019001,
                'seller' => $contacts['me'],
                'buyer' => $contacts['company1'],
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
                'seller' => $contacts['me'],
                'buyer' => $contacts['company2'],
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
            $this->getLoader()->getAllInvoices(self::INPUT_DIRECTORY)
        );
    }

    public function testLoadsAllContacts()
    {
        $expectedInvoices = [
            'me' => Contact::create([
                'id' => 'me',
                'name' => 'Ing. Petr Knap',
                'addressLine1' => 'Dlouhá 572',
                'city' => 'Trutnov',
                'zipOrPostalCode' => '54102',
                'country' => 'Czech Republic',
                'email' => 'kontakt@petrknap.cz',
                'identificationNumber' => 12345678,
                'registrationNumberInCompanyRegister' => 'S-SMO/123456/78/ŽÚ',
                'ban' => '123/0100',
                'iban' => 'CZ7801000000000000000123',
            ]),
            'company1' => Contact::create([
                'id' => 'company1',
                'name' => 'Company #1',
                'addressLine1' => 'Hrnčířská 573/6',
                'addressLine2' => 'Královo Pole',
                'city' => 'Brno',
                'zipOrPostalCode' => '60600',
                'country' => 'Czech Republic',
                'email' => 'company1@example.com',
                'identificationNumber' => 90123456,
            ]),
            'company2' => Contact::create([
                'id' => 'company2',
                'name' => 'Company #2',
                'addressLine1' => 'P.O. Box 558',
                'addressLine2' => '9561 Lacus. Road',
                'city' => 'Laughlin',
                'zipOrPostalCode' => '99602',
                'stateOrProvinceOrRegion' => 'Hawaii',
                'country' => 'United States of America',
                'email' => 'company2@example.com',
            ]),
        ];
        $this->assertEquals(
            $expectedInvoices,
            $this->getLoader()->getAllContacts(self::INPUT_DIRECTORY)
        );
    }

    private function getLoader(): Loader
    {
        return new Loader(new Serializer([],[new CsvEncoder()]));
    }
}
