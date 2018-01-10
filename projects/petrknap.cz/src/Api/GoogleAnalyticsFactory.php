<?php

namespace PetrKnapCz\Api;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

class GoogleAnalyticsFactory
{
    public static function create(Request $request, string $trackingId)
    {
        if ($request->cookies->has('ga_client_id')) {
            $clientId = $request->cookies->get('ga_client_id');
        } else {
            $clientId = Uuid::uuid4()->toString();
            $response = new Response();
            $response->headers->setCookie(new Cookie('ga_client_id', $clientId, new \DateTime('+2 years')));
            $response->sendHeaders();
        }

        $analytics = (new Analytics(true))
            ->setAsyncRequest(true)
            ->setProtocolVersion('1') // https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
            ->setTrackingId($trackingId)
            ->setDataSource('web')
            ->setClientId($clientId)
            ->setIpOverride($request->getClientIp())
            ->setUserLanguage($request->getPreferredLanguage())
            ->setHitType('pageview')
            ->setDocumentHostName($request->getHost())
            ->setDocumentPath($request->getRequestUri());

        if ($request->headers->has('User-Agent')) {
            $analytics->setUserAgentOverride($request->headers->get('User-Agent'));
        }

        if ($request->headers->has('Referer')) {
            $analytics->setUserAgentOverride($request->headers->get('Referer'));
        }

        return $analytics;
    }
}
