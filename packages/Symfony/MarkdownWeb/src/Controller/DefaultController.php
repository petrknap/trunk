<?php

namespace PetrKnap\Symfony\MarkdownWeb\Controller;

use const PetrKnap\Symfony\MarkdownWeb\CONFIG;
use const PetrKnap\Symfony\MarkdownWeb\CONTROLLER_CACHE;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    const ROUTE = "markdown_web";

    /**
     * @Route("/{url}", defaults={"url" = ""}, requirements={"url"=".*"}, name="markdown_web")
     * @param Request $request
     * @param string $url
     * @return Response
     */
    public function defaultAction(Request $request, $url)
    {
        $pageNumber = $request->get("page");
        if (1 == $pageNumber) {
            return $this->redirectToRoute(
                static::ROUTE,
                [
                    "url" => $url
                ]
            );
        }
        $pageNumber = null === $pageNumber ? 1 : $pageNumber;
        $url = $this->urlModifier("/" . $url);

        $config = $this->get(CONFIG);
        if ($config['cache']['enabled']) { // TODO test it
            /** @var AdapterInterface $cache */
            $cache = $this->get(CONTROLLER_CACHE);
            $cached = $cache->getItem(str_replace(
                ['+', '/', '='],
                ['_', '-', ''],
                base64_encode(sprintf("%s?page=%s", $url, $pageNumber))
            ));

            if (!$cached->isHit()) {
                $cached->set($this->createResponse($url, $pageNumber));
                $cache->save($cached);
            }

            return $cached->get();
        } else { // TODO test it
            return $this->createResponse($url, $pageNumber);
        }
    }

    private function createResponse($url, $pageNumber)
    {
        /** @var Crawler $crawler */
        $crawler = $this->get(CRAWLER_SERVICE);
        $page = $crawler->getIndex([$this, "urlModifier"])->getPage($url);

        if (!$page) {
            throw new NotFoundHttpException("Page with url '{$url}' not found");
        }

        return $page->getResponse(function ($view, $parameters) use ($url, $pageNumber) {
            $config = $this->get(CONFIG);

            /** @noinspection PhpInternalEntityUsedInspection */
            return $this->renderView($view, [
                    "url" => $url,
                    "page_number" => $pageNumber,
                    "site" => $config["site"],
                    "base_uri" => $this->urlModifier("/"),
                ] + $parameters);
        });
    }

    public function urlModifier($url)
    {
        return $this->generateUrl(
            static::ROUTE,
            [
                "url" => substr($url, 1)
            ]
        );
    }
}
