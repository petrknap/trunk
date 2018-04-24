<?php

namespace Storyboard;

use PHPImageWorkshop\Exception\ImageWorkshopBaseException;
use PHPImageWorkshop\ImageWorkshop;

class ImageFileProcessor
{
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * @inheritdoc
     */
    public function processFile($pathToFile)
    {
        $newFileName = sha1_file($pathToFile) . '.jpg';

        try {
            $this->resizeImage($pathToFile)->save($this->targetDir, $newFileName);
        } catch (ImageWorkshopBaseException $e) {
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }

        return '<img class="card-img" src="./' . $newFileName . '" alt="' . $pathToFile . '">';
    }

    private function resizeImage($pathToFile)
    {
        $image = ImageWorkshop::initFromPath($pathToFile);
        $image->resizeByLargestSideInPixel(720, true);

        return $image;
    }
}
