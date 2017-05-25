<?php

namespace PetrKnap\Symfony\MarkdownWeb\Controller;

use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_ALIAS;
use const PetrKnap\Symfony\MarkdownWeb\CRAWLER_SERVICE;

class DefaultController extends Controller
{
    /**
     * @Route("/{url}", defaults={"url" = ""}, requirements={"url"=".*"})
     * @param Request $request
     * @param string $url
     * @return Response
     */
    public function defaultAction(Request $request, $url)
    {
        $page = $request->get("page");
        if (1 == $page) {
            return $this->redirectToRoute(
                $request->get('_route'),
                [
                    "url" => $url
                ]
            );
        }
        $page = null === $page ? 1 : $page;
        $url = "/" . $url;

        if ($this->getParameter(BUNDLE_ALIAS . ".cached")) { // TODO test it
            $cache = new FilesystemAdapter(BUNDLE_ALIAS . ".default_controller");
            $cached = $cache->getItem(base64_encode(sprintf("%s?page=%s", $url, $page)));

            if (!$cached->isHit()) {
                $cached->set($this->createResponse($url, $page));
                $cache->save($cached);
            }

            return $cached->get();
        } else { // TODO test it
            return $this->createResponse($url, $page);
        }
    }

    private function createResponse($url, $pageNumber)
    {
        /** @var Crawler $crawler */
        $crawler = $this->container->get(CRAWLER_SERVICE);
        $page = $crawler->getIndex()->getPage($url);

        if (!$page) {
            throw new NotFoundHttpException();
        }

        try {
            $site = $this->getParameter(BUNDLE_ALIAS . ".site");
        } catch (InvalidArgumentException $ignored) {
            $site = [];
        }

        return $page->getResponse(function ($view, $parameters) use ($url, $pageNumber, $site) {
            return $this->renderView($view, [
                    "url" => $url,
                    "page_number" => $pageNumber,
                    "site" => $site
                ] + $parameters);
        });
    }
}
