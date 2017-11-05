<?php

namespace Storyboard;

interface FileProcessor
{
    /**
     * Processes file and return HTML output
     *
     * @param string $pathToFile
     * @return string
     */
    public function processFile($pathToFile);
}
