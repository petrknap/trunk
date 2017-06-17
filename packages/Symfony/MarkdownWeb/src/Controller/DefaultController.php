<?php

namespace PetrKnap\Symfony\MarkdownWeb\Controller;

use const PetrKnap\Symfony\MarkdownWeb\CONFIG;
use const PetrKnap\Symfony\MarkdownWeb\CONTROLLER;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @var string|null
     */
    private $route;

    /**
     * @Route("/{url}", defaults={"url" = ""}, requirements={"url"=".*"})
     * @param Request $request
     * @param string $url
     * @return Response
     */
    public function defaultAction(Request $request, $url)
    {
        $this->route = $request->get('_route');
        $pageNumber = $request->get("page");
        if (1 == $pageNumber) {
            return $this->redirectToRoute(
                $this->route,
                [
                    "url" => $url
                ]
            );
        }
        $pageNumber = null === $pageNumber ? 1 : $pageNumber;
        $url = $this->urlModifier("/" . $url);

        $config = $this->get(CONFIG);
        if ($config['cached']) { // TODO test it
            $cache = new FilesystemAdapter(CONTROLLER);
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
            $this->route,
            [
                "url" => substr($url, 1)
            ]
        );
    }
}
