<?php

namespace PetrKnapCz;

class RemoteContent
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var int
     */
    private $status;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var mixed
     */
    private $content;

    public function __construct(string $url, int $status, array $headers, $content)
    {
        $this->url = $url;
        $this->status = $status;
        $this->headers = $headers;
        $this->content = $content;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getContent()
    {
        return $this->content;
    }
}
