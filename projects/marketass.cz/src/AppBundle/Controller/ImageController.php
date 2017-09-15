<?php

namespace AppBundle\Controller;

use const PetrKnap\Symfony\MarkdownWeb\CONTROLLER_CACHE;
use PHPImageWorkshop\ImageWorkshop;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property ContainerInterface container
 */
class ImageController extends Controller
{
    const SIZES = [
        'well_header' => [960, 320],
        'shop_preview' => [250, 150],
        'gallery_preview' => [280, 360],
    ];

    /**
     * @Route("/image/{size}/{path}.png", requirements={"path"=".*"}, name="image")
     * @return Response
     */
    public function imageAction($size, $path)
    {
        if (!isset(static::SIZES[$size])) {
            throw new NotFoundHttpException("Unknown size '{$size}'");
        }

        $realPath = realpath(__DIR__ . '/../../../www/' . str_replace('..', '', $path));
        $cacheKey = str_replace(
            ['+', '/', '='],
            ['_', '-', ''],
            base64_encode(sprintf("%s?size=%s", $realPath, $size))
        );

        if (!file_exists($realPath)) {
            throw new NotFoundHttpException("File '{$path}' does not exists");
        }

        /** @var AdapterInterface $cache */
        $cache = $this->get(CONTROLLER_CACHE);
        $cached = $cache->getItem($cacheKey);
        if (!$cached->isHit()) {
            $cached->set(new Response(
                $this->resizeImage($realPath, static::SIZES[$size]),
                Response::HTTP_OK,
                [
                    'Content-type' => 'image/png',
                    'Cache-Control' => 'public, must-revalidate',
                ]
            ));
            $cache->save($cached);
        }

        return $cached->get();
    }

    private function resizeImage($path, $dimensions)
    {
        $image = ImageWorkshop::initFromPath($path);
        $width = $image->getWidth();
        $height = $image->getHeight();
        $pX = 0;
        $pY = 0;

        if ($width > $height) {
            $image->resizeByLargestSideInPixel($dimensions[0], true);
            if ($image->getHeight() > $dimensions[1]) {
                $image->resizeInPixel(null, $dimensions[1], true);
            }
        } else {
            $image->resizeByLargestSideInPixel($dimensions[1], true);
            if ($image->getWidth() > $dimensions[0]) {
                $image->resizeInPixel(null, $dimensions[0], true);
            }
        }

        $pX -= ($dimensions[0] - $image->getWidth()) / 2;
        $pY -= ($dimensions[1] - $image->getHeight()) / 2;

        $image->cropInPixel($dimensions[0], $dimensions[1], $pX, $pY);

        ob_start();
        imagepng($image->getImage());
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
