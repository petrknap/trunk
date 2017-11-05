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

    public function getRecord(string $short): UrlShortenerRecord
    {
        $statement = $this->database->prepare('-- noinspection SqlDialectInspection
SELECT id, short, long, is_redirect FROM url_shortener__records WHERE short = ?');
        $statement->execute([$short]);
        $data = $statement->fetch(\PDO::FETCH_NUM);

        if (false === $data) {
            throw new RecordNotFoundException("Record for short '{$short}' not found");
        } else {
            return new UrlShortenerRecord(...$data);
        }
    }
}
