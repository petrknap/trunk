<?php

use AppBundle\Service\RemoteContentAccessor;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollectionBuilder;

class WwwKernel extends AppKernel
{
    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/', 'kernel:indexAction');
    }

    public function indexAction()
    {
        $cache = new FilesystemAdapter(__CLASS__, 7 * 24 * 3600, $this->getCacheDir());
        $cacheItem = $cache->getItem('index');

        if (!$cacheItem->isHit()) {
            $content = $this->getContainer()
                ->get(RemoteContentAccessor::class)
                ->getRemoteContent('https://petrknap.github.io/index_cz.html');

            $cacheItem->set(new Response($content));
            $cache->save($cacheItem);
        }

        return $cacheItem->get();
    }
}
