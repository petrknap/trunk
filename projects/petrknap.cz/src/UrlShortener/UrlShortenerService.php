<?php

namespace PetrKnapCz\UrlShortener;

use PetrKnapCz\RemoteContent\RemoteContentAccessor;
use PetrKnapCz\UrlShortener\Exception\RecordNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UrlShortenerService
{
    /**
     * @var \PDO
     */
    private $database;

    /**
     * @var RemoteContentAccessor
     */
    private $remoteContentAccessor;

    public function __construct(\PDO $database, RemoteContentAccessor $remoteContentAccessor)
    {
        $this->database = $database;
        $this->remoteContentAccessor = $remoteContentAccessor;
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

    public function getResponse(string $keyword): Response
    {
        try {
            $record = $this->getRecord($keyword);

            if ($record->isRedirect()) {
                return new RedirectResponse($record->getUrl());
            } else {
                return $this->remoteContentAccessor->getResponse($record->getUrl());
            }
        } catch (RecordNotFoundException $e) {
            return new Response($e->getMessage(), Response::HTTP_NOT_FOUND, [
                'Content-Type' => 'text/plain; charset=utf-8'
            ]);
        }
    }
}
