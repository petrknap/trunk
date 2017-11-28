<?php

namespace PetrKnapCz\UrlShortener;

use PetrKnapCz\TestCase;
use PetrKnapCz\UrlShortener\Exception\RecordNotFoundException;

class UrlShortenerServiceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        /** @var \PDOStatement $statement */
        $statement = $this->get(\PDO::class)->prepare("-- noinspection SqlDialectInspection
INSERT INTO url_shortener__records (id, keyword, url, is_redirect) VALUES (?, ?, ?, ?)");

        $statement->execute([1, 'keyword', 'url', false]);
        $statement->execute([2, 'redirect_keyword', 'redirect_url', true]);
    }

    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            UrlShortenerService::class,
            $this->get(UrlShortenerService::class)
        );
    }

    /**
     * @dataProvider dataGetRecord_returnsRecordWhenRecordExists
     * @param UrlShortenerRecord $expected
     * @param string $short
     */
    public function testGetRecord_returnsRecordWhenRecordExists(UrlShortenerRecord $expected, $short)
    {
        $this->assertEquals(
            $expected,
            $this->get(UrlShortenerService::class)->getRecord($short)
        );
    }

    public function dataGetRecord_returnsRecordWhenRecordExists()
    {
        return [
            [
                new UrlShortenerRecord(1, 'keyword', 'url', false),
                'keyword'
            ],
            [
                new UrlShortenerRecord(2, 'redirect_keyword', 'redirect_url', true),
                'redirect_keyword'
            ],
        ];
    }

    public function testGetRecord_throwsExceptionWhenRecordDoesNotExist()
    {
        $this->expectException(RecordNotFoundException::class);

        $this->get(UrlShortenerService::class)->getRecord('not_found');
    }

    public function testGetResponse_TODO()
    {
        $this->markTestIncomplete();
    }
}
