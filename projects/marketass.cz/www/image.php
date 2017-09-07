<?php

use PHPImageWorkshop\ImageWorkshop;

require_once __DIR__ . '/../vendor/autoload.php';

$path = __DIR__ . str_replace('..', '', $_GET['path']);

switch ($_GET['size']) {
    case 'shop_preview':
        $w = 250;
        $h = 150;
        break;
    case 'gallery_preview':
        $w = 280;
        $h = 360;
        break;
    default:
        throw new Exception('Unknown size');
}

if (file_exists($path)) {
    $image = ImageWorkshop::initFromPath($path);
    $width = $image->getWidth();
    $height = $image->getHeight();
    $pX = 0;
    $pY = 0;

    if ($width > $height) {
        $image->resizeByLargestSideInPixel($w, true);
        if ($image->getHeight() > $h) {
            $image->resizeInPixel(null, $h, true);
        }
    } else {
        $image->resizeByLargestSideInPixel($h, true);
        if ($image->getWidth() > $w) {
            $image->resizeInPixel(null, $w, true);
        }
    }

    $pX -= ($w - $image->getWidth()) / 2;
    $pY -= ($h - $image->getHeight()) / 2;

    $image->cropInPixel($w, $h, $pX, $pY);

    header('Content-type: image/png');
    header('Content-Disposition: filename="image.png"');
    imagepng($image->getImage());
} else {
    throw  new Exception('Unknown path');
}
