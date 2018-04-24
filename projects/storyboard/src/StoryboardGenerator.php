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
                return strcmp($a->getRealPath(), $b->getRealPath());
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
            ->name('/\.(log|jpg|png)$/')
            ->sort(function (\SplFileInfo $a, \SplFileInfo $b) {
                return strcmp($a->getRealPath(), $b->getRealPath());
            })
            ->getIterator();
        $title = basename($subDir);
        $id = md5($subDir);
        $content = '';
        foreach ($files as $file) {
            $content .= $this->generateItem($file);
        }

        ob_start();
        {
            include __DIR__ . '/templates/section.phtml';
            $content = ob_get_contents();
        }
        ob_end_clean();

        return $content;
    }

    private function generateItem(\SplFileInfo $file)
    {
        $logFileProcessor = new LogFileProcessor();
        $imageFileProcessor = new ImageFileProcessor($this->targetDir);

        $title = $file->getBasename();
        $id = md5($file->getRealPath());
        switch ($file->getExtension()) {
            case 'log':
                $content = $logFileProcessor->processFile($file->getRealPath());
                break;
            case 'jpg':
            case 'png':
                $content = $imageFileProcessor->processFile($file->getRealPath());
                break;
            default:
                $content = '';
        }

        ob_start();
        {
            include __DIR__ . '/templates/item.phtml';
            $content = ob_get_contents();
        }
        ob_end_clean();

        return $content;
    }
}
