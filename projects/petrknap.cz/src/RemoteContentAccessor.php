<?php

namespace PetrKnapCz;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

class RemoteContentAccessor
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getRemoteContent(string $url): RemoteContent
    {
        $handler = curl_init();

        curl_setopt($handler, CURLOPT_URL, $url);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handler, CURLOPT_HEADER, true);

        $response = curl_exec($handler);
        $status = curl_getinfo($handler, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($handler, CURLINFO_HEADER_SIZE);

        $headers = explode("\n", substr($response, 0, $headerSize));
        $content = substr($response, $headerSize);

        $tmp = [];
        foreach ($headers as $header) {
            $header = explode(':', $header, 2);
            if (2 === count($header)) {
                $headerKey = trim($header[0]);
                $headerValue = trim($header[1]);
                switch ($headerKey) {
                    case 'Content-Type':
                        $tmp[$headerKey] = $headerValue;
                }
            }
        }
        $headers = $tmp;

        curl_close($handler);

        return new RemoteContent($url, $status, $headers, $content ?: null);
    }

    public function getResponse(string $url): Response
    {
        $cached = $this->cache->getItem(urlencode($url));

        if (!$cached->isHit()) {
            $remoteContent = $this->getRemoteContent($url);

            $cached->set(new Response(
                $remoteContent->getContent(),
                $remoteContent->getStatus(),
                $remoteContent->getHeaders()
            ));

            if (200 <= $remoteContent->getStatus() && 300 > $remoteContent->getStatus()) {
                $this->cache->save($cached);
            }
        }

        return $cached->get();
    }
}
