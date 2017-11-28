<?php

namespace PetrKnapCz\UrlShortener;

use PetrKnapCz\UrlShortener\Exception\RecordNotFoundException;

class UrlShortenerService
{
    /**
     * @var \PDO
     */
    private $database;

    public function __construct(\PDO $database)
    {
        $this->database = $database;
    }

    public function getRecord(string $keyword): UrlShortenerRecord
    {
        $statement = $this->database->prepare('-- noinspection SqlDialectInspection
SELECT id, keyword, url, is_redirect FROM url_shortener__records WHERE keyword = ?');
        $statement->execute([$keyword]);
        $data = $statement->fetch(\PDO::FETCH_NUM);

        if (false === $data) {
            throw new RecordNotFoundException("Record for keyword '{$keyword}' not found");
        } else {
            return new UrlShortenerRecord(...$data);
        }
    }
}
