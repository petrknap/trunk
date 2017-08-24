<?php

namespace PetrKnap\Symfony\MarkdownWeb\Model;

use Mni\FrontYAML\Parser;
use PetrKnap\Symfony\MarkdownWeb\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use const PetrKnap\Symfony\MarkdownWeb\BUNDLE_NAME;

class Page
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $content;

    /**
     * @param array $parameters
     * @param string $content
     */
    public function __construct(array $parameters, $content)
    {
        $this->parameters = $parameters;
        $this->content = $content;

        // Make easy access to properties from twig
        foreach ($this->parameters as $name => $value) {
            $this->{$name} = $value;
        }
    }

    /**
     * @param string $rootDirectory
     * @param string $pathToFile
     * @param callable $urlModifier
     * @return $this|null
     */
    public static function fromFile($rootDirectory, $pathToFile, callable $urlModifier)
    {
        $file = new \SplFileInfo($pathToFile);

        $content = @file_get_contents($pathToFile);
        if (false === $content) {
            return null;
        }

        $document = (new Parser())->parse($content, true);
        $parameters = (array)$document->getYAML();
        $content = $document->getContent();

        $layout = @$parameters['layout'];
        if (isset($layout)) {
            $extension = substr($layout, strrpos($layout, '.') + 1);
        }

        if (!isset($extension)) {
            $extension = "html";
        }

        $url = str_replace(
            [(new \SplFileInfo($rootDirectory))->getRealPath(), $file->getExtension(), "index.html"],
            ["", $extension, ""],
            $file->getRealPath()
        );

        $parameters = array_merge($parameters, [
            "url" => call_user_func($urlModifier, $url),
            "extension" => $extension,
        ]);

        return new static($parameters, $content);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param callable $twigRenderer
     * @return Response
     */
    public function getResponse($twigRenderer)
    {
        $parameters = $this->getParameters();

        $content = $twigRenderer(
            "@" . BUNDLE_NAME . "/" . $parameters["layout"] . ".twig",
            [
                "content" => $this->content,
                "page" => $this->parameters
            ]
        );

        $response = new Response(
            $content,
            Response::HTTP_OK
        );

        switch ($parameters["extension"]) {
            case "xml":
                $contentType = "application/xml";
                break;
            default:
                $contentType = "text/html";
                break;
        }

        $response->headers->add(["Content-Type" => $contentType]);

        return $response;
    }

    /**
     * @param string $name
     * @param array $args ignored
     * @return mixed
     */
    public function __call($name, array $args)
    {
        throw new RuntimeException(sprintf(
            'Accessing unknown method \'%s\' of page \'%s\'',
            $name,
            $this->parameters['url']
        ));
    }
}
