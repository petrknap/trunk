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
        $nonLogFiles = [];
        $index = 1;
        foreach ($files as $file) {
            switch ($file->getExtension()) {
                case 'log':
                    $section .= $this->generateRow($file->getRealPath(), $nonLogFiles, $index % 2);
                    $nonLogFiles = [];
                    break;
                case 'jpg':
                case 'png':
                    $nonLogFiles[] = $file->getRealPath();
                    break;
            }
            $index++;
        }
        $section .= $this->generateRow(null, $nonLogFiles, $index % 2);

        $title = basename($subDir);
        $content = $section;

        ob_start();
        include __DIR__ . '/templates/section.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    private function generateRow($logFile, $nonLogFiles, $logLeft)
    {
        if ($logFile) {
            $logContent = (new LogFileProcessor())->processFile($logFile);
        } else {
            $logContent = null;
        }

        $nonLogContent = ''; // TODO

        if (!$logContent) {
            $leftContent = $nonLogContent;
            $rightContent = null;
        } elseif (!$nonLogContent) {
            $leftContent = $logContent;
            $rightContent = null;
        } elseif ($logLeft) {
            $leftContent = $logContent;
            $rightContent = $nonLogContent;
        } else {
            $leftContent = $nonLogContent;
            $rightContent = $logContent;
        }

        ob_start();
        include __DIR__ . '/templates/row.phtml';
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
