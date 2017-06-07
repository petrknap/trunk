<?php

namespace PetrKnap\Symfony\MarkdownWeb\Service;

use PetrKnap\Symfony\MarkdownWeb\Model\Index;

class Crawler
{
    const SUPPORTED_FILES = ["*.md"];

    /**
     * @var string
     */
    private $directory;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param $directory
     * @return \string[]
     */
    private function getFiles($directory)
    {
        $rdi = new \RecursiveDirectoryIterator(
            $directory,
            \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS
        );

        $files = [];
        foreach (new \RecursiveIteratorIterator($rdi) as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            $file = $fileInfo->getRealPath();

            foreach (static::SUPPORTED_FILES as $pattern) {
                if (fnmatch($pattern, $file)) {
                    $files[] = $file;
                    break;
                }
            }
        }

        return $files;
    }

    /**
     * @param callable $urlModifier
     * @return Index
     */
    public function getIndex(callable $urlModifier)
    {
        static $index;

        if (null === $index) {
            /** @noinspection PhpParamsInspection */
            $index = Index::fromFiles(
                $this->directory,
                $this->getFiles($this->directory),
                $urlModifier
            );
        }

        return $index;
    }
}
