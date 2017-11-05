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
    private $short;

    /**
     * @var string
     */
    private $long;
    /**
     * @var bool
     */
    private $redirect;

    public function __construct(int $id, string $short, string $long, bool $isRedirect)
    {
        $this->id = $id;
        $this->short = $short;
        $this->long = $long;
        $this->redirect = $isRedirect;
    }
}
