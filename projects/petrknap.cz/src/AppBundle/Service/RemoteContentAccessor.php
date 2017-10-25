<?php

namespace AppBundle\Service;

class RemoteContentAccessor
{
    public function getRemoteContent($uri)
    {
        $handler = curl_init();

        curl_setopt($handler, CURLOPT_URL, $uri);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_FOLLOWLOCATION, true);

        $output = curl_exec($handler);
        $httpCode = curl_getinfo($handler, CURLINFO_HTTP_CODE);

        curl_close($handler);

        if (200 <= $httpCode && 300 > $httpCode) {
            return $output;
        }

        return null;
    }
}
