<?php

namespace PetrKnapCz;

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

    /**
     * @var string
     */
    private $forcedContentType;

    public function __construct(int $id, string $keyword, string $url, bool $isRedirect, string $forcedContentType = null)
    {
        $this->id = $id;
        $this->keyword = $keyword;
        $this->url = $url;
        $this->redirect = $isRedirect;
        $this->forcedContentType = $forcedContentType;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isRedirect(): bool
    {
        return $this->redirect;
    }

    public function hasForcedContentType(): bool
    {
        return null !== $this->forcedContentType;
    }

    public function getForcedContentType(): string
    {
        return $this->forcedContentType;
    }
}
