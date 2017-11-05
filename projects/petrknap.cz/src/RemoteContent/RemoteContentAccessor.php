<?php

namespace PetrKnapCz\RemoteContent;

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

    /**
     * @param string $uri
     * @return Response
     */
    public function getRemoteContent($uri)
    {
        $cached = $this->cache->getItem(urlencode($uri));

        if (!$cached->isHit()) {
            $handler = curl_init();

            curl_setopt($handler, CURLOPT_URL, $uri);
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
                $headerKey = trim($header[0]);
                $headerValue = trim($header[1]);
                switch ($headerKey) {
                    case 'Content-Type':
                        $tmp[$headerKey] = $headerValue;
                }
            }
            $headers = $tmp;

            curl_close($handler);

            $cached->set(new Response($content, $status, $headers));

            if (200 <= $status && 300 > $status) {
                $this->cache->save($cached);
            }
        }

        return $cached->get();
    }
}
