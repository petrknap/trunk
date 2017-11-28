<?php

namespace PetrKnapCz\UrlShortener;

class UrlShortenerRecord
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $keyword;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $redirect;

    public function __construct(int $id, string $keyword, string $url, bool $isRedirect)
    {
        $this->id = $id;
        $this->keyword = $keyword;
        $this->url = $url;
        $this->redirect = $isRedirect;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isRedirect(): bool
    {
        return $this->redirect;
    }
}
