<?php

namespace Storyboard;

interface FileProcessor
{
    /**
     * Processes file and returns HTML output
     *
     * @param string $pathToFile
     * @return string
     */
    public function processFile($pathToFile);
}
