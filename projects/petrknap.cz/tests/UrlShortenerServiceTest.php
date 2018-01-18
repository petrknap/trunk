<?php

namespace PetrKnapCz\Test;

use PetrKnapCz\Exception\UrlShortenerRecordNotFoundException;
use PetrKnapCz\UrlShortenerRecord;
use PetrKnapCz\UrlShortenerService;

class UrlShortenerServiceTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        /** @var \PDOStatement $statement */
        $statement = $this->get(\PDO::class)->prepare("-- noinspection SqlDialectInspection
INSERT INTO url_shortener__records (id, keyword, url, is_redirect, forced_content_type) VALUES (?, ?, ?, ?, ?)");

        $statement->execute([1, 'keyword', 'url', false, null]);
        $statement->execute([2, 'redirect_keyword', 'redirect_url', true, null]);
        $statement->execute([3, 'redirect_keyword_with_forced_content_type', 'redirect_url', true, 'text/plain; charset=iso-8859-2']);
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
                new UrlShortenerRecord(1, 'keyword', 'url', false, null),
                'keyword'
            ],
            [
                new UrlShortenerRecord(2, 'redirect_keyword', 'redirect_url', true, null),
                'redirect_keyword'
            ],
            [
                new UrlShortenerRecord(3, 'redirect_keyword', 'redirect_url', true, null),
                'redirect_keyword_with_forced_content_type'
            ],
        ];
    }

    public function testGetRecord_throwsExceptionWhenRecordDoesNotExist()
    {
        $this->expectException(UrlShortenerRecordNotFoundException::class);

        $this->get(UrlShortenerService::class)->getRecord('not_found');
    }

    public function testGetResponse_TODO()
    {
        $this->markTestIncomplete();
    }
}
