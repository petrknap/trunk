<?php

namespace PetrKnap\Symfony\MarkdownWeb\Controller;

use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
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
        $pageNumber = $request->get("page");
        if (1 == $pageNumber) {
            return $this->redirectToRoute(
                $request->get('_route'),
                [
                    "url" => $url
                ]
            );
        }
        $pageNumber = null === $pageNumber ? 1 : $pageNumber;
        $url = "/" . $url;

        $config = $this->get(BUNDLE_ALIAS . '.config');
        if ($config['cached']) { // TODO test it
            $cache = new FilesystemAdapter(BUNDLE_ALIAS . ".default_controller");
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
        $crawler = $this->container->get(CRAWLER_SERVICE);
        $page = $crawler->getIndex()->getPage($url);

        if (!$page) {
            throw new NotFoundHttpException();
        }

        return $page->getResponse(function ($view, $parameters) use ($url, $pageNumber) {
            $config = $this->get(BUNDLE_ALIAS . ".config");
            return $this->renderView($view, [
                    "url" => $url,
                    "page_number" => $pageNumber,
                    "site" => $config["site"]
                ] + $parameters);
        });
    }
}
