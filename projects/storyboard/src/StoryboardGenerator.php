<?php

namespace Storyboard;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class StoryboardGenerator
{
    private $sourceDir;
    private $targetDir;
    private $fileSystem;

    public function __construct($sourceDir, $targetDir)
    {
        $this->sourceDir = $sourceDir;
        $this->targetDir = $targetDir;
        $this->fileSystem = new Filesystem();
    }

    public function generate()
    {
        $title = basename($this->sourceDir);
        $content = $this->generateBody();
        ob_start();
        include __DIR__ . '/templates/index.phtml';
        $content = ob_get_contents();
        ob_end_clean();
        $this->fileSystem->dumpFile(
            $this->targetDir . DIRECTORY_SEPARATOR . 'index.html',
            $content
        );
    }
    private function generateBody()
    {
        $dirs = Finder::create()
            ->directories()
            ->in($this->sourceDir)
            ->depth(0)
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                return strcmp($a->getBasename(), $b->getBasename());
            })
            ->getIterator();
        $body = '';
        foreach ($dirs as $dir) {
            $body .= $this->generateSection($dir->getRealPath());
        }
        return $body;
    }

    private function generateSection($subDir)
    {
        $files = Finder::create()
            ->files()
            ->in($subDir)
            ->name('/\.(log)$/')
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                return strcmp($a->getRealPath(), $b->getRealPath());
            })
            ->getIterator();
        $section = "";
        $content = [];
        $logFileProcessor = new LogFileProcessor();
        foreach ($files as $file) {
            switch ($file->getExtension()) {
                case 'log':
                    $content[] = $logFileProcessor->processFile($file->getRealPath());
                    break;
                case 'jpg':
                case 'png':
                    // TODO
                    break;
            }
        }
        for ($i = 0; $i < count($content); $i = $i + 2) {
            $leftContent = $content[$i];
            $rightContent = isset($content[$i+1]) ? $content[$i+1] : null;
            $section .= $this->generateRow($leftContent, $rightContent);
        }

        $title = basename($subDir);
        $content = $section;

        ob_start();
        include __DIR__ . '/templates/section.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    private function generateRow($leftContent, $rightContent)
    {
        ob_start();
        include __DIR__ . '/templates/row.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
