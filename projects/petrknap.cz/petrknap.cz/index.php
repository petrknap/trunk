<?php

namespace PetrKnapCz;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

function getAsXml(string $htmlContent): \SimpleXMLElement
{
    $domContent = new \DOMDocument();
    @$domContent->loadHTML($htmlContent);
    return simplexml_import_dom($domContent);
}

function convertSlidesIntoPages(\SimpleXMLElement $xmlContent): array
{
    $pages = [];
    $sections = $xmlContent->xpath('//div[contains(@class, "slides")]/section');
    foreach ($sections as $section) {
        $content = clone $section;
        if (isset($content->h2)) {
            $title = clone $content->h2[0];
            unset($content->h2[0]);
        } else {
            $title = clone $content->section[0]->h2[0];
            unset($content->section[0]->h2[0]);
            unset($content->section[0]);
        }

        $id = empty($pages) ? "" : slugify((string) $title['id']);
        $pages[$id] = [
            'uri' => $id ? "{$id}.html" : '',
            'title' => (string) $title,
            'content' => $content->asXML(),
            'section' => $section,
        ];
    }

    return $pages;
}

function convertPagesIntoSlides(array $pages): string
{
    $slides = '';
    foreach ($pages as $id => $page) {
        $page['section']['id'] = "slide_{$id}";
        $slides .= $page['section']->asXML();
    }

    return $slides;
}

function injectPage(string $htmlContent, string $pageId = null): string
{
    if (!$pageId) {
        $pageId = '';
    }

    $xmlContent = getAsXml($htmlContent);
    $pages = convertSlidesIntoPages($xmlContent);
    $slides = convertPagesIntoSlides($pages);
    $scripts = $xmlContent->xpath('//body/script');
    $footer = $xmlContent->xpath('//body/footer');

    if (!array_key_exists($pageId, $pages)) {
        (new Response('Page Not Found', Response::HTTP_NOT_FOUND, [
            'Content-Type' => 'text/plain',
        ]))->send();
        die;
    }

    $page = $pages[$pageId];
    $menu = '';
    foreach ($pages as $_id => $_page) {
        $_active = $_id == $pageId ? 'active' : '';
        $menu .= "<a href='./{$_page['uri']}' class='list-group-item {$_active}'>{$_page['title']}</a>";
    }
    $body = '
<div id="content" class="reveal">
    <div class="container well-sm" style="background-color: white; padding: 25px; margin-top: 25px">
        <div class="row">
            <div class="col-md-8">
                <h1>' . $page['title'] . '</h1>
                ' . $page['content'] . '
            </div>
            <div class="col-md-4">
                <div class="list-group">' . $menu . '</div>
            </div>
        </div>
    </div>
</div>
' . $footer[0]->asXML() . '
<script>
    document.getElementById("content").innerHTML = "<div class=\'slides\'>" + ' . json_encode($slides) . ' + "</div>";
</script>
';
    foreach ($scripts as $script) {
        $body .= preg_replace(
                [
                    '/\/>$/',
                    '/<script><!\[CDATA\[/',
                    '/\]\]><\/script>/'
                ],
                [
                    '></script>',
                    '<script>',
                    '</script>',
                ],
                $script->asXML()
            ) . PHP_EOL;
    }
    $body .= '
<script>
    $(function() {
        var indices = Reveal.getIndices( document.getElementById("slide_' . $pageId . '") );
        Reveal.slide( indices.h, indices.v );
    });
    window.history.pushState("", "", "./");
</script>
';

    return preg_replace('/<body>.*<\/body>/uis', "<body>{$body}</body>", $htmlContent);
}

$request = Request::createFromGlobals();
$pageId = $request->query->get('page', '');
$sitemap = $request->query->get('sitemap', false);

$response = container()
    ->get(RemoteContentAccessor::class)
    ->getResponse('https://petrknap.github.io/index_cz.html');
$content = $response->getContent();

if ($sitemap) {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
' . implode('', array_map(function (array $page) use ($request) {
 return '<url><loc>' . str_replace('/sitemap.xml?sitemap=xml', '/' . $page['uri'], $request->getUri()) . '</loc></url>';
}, convertSlidesIntoPages(getAsXml($content)))) . '
</urlset>';
    $response = new Response($sitemap, Response::HTTP_OK, [
        'Content-Type' => 'application/xml'
    ]);
    $response->send();
    die;
}

$response->setContent(injectPage($content, $pageId))->send();
