<?php

namespace PetrKnap\Nette\Bootstrap\PhpUnit;

use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use PetrKnap\Nette\Bootstrap\Bootstrap;

abstract class NetteTestCase extends \PHPUnit_Framework_TestCase
{
    const NETTE_BOOTSTRAP_CLASS = null; // string

    /**
     * @var Container
     */
    private static $container;

    /**
     * @return string
     */
    protected static function getBootstrapClass()
    {
        if (static::NETTE_BOOTSTRAP_CLASS !== null) {
            return static::NETTE_BOOTSTRAP_CLASS;
        } elseif (defined("NETTE_BOOTSTRAP_CLASS")) {
            return NETTE_BOOTSTRAP_CLASS;
        } else {
            throw new \RuntimeException("Unknown Nette Bootstrap class");
        }
    }

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $me = new static();
        $me->clearTemp();

        self::$container = call_user_func(self::getBootstrapClass() . "::getContainer", array(
            Bootstrap::OPTION_IS_TEST_RUN => true
        ));
    }

    /**
     * @return Container
     */
    protected function getContainer()
    {
        return self::$container;
    }

    /**
     * @return void
     */
    public function clearTemp()
    {
        /** @var Bootstrap $bootstrap */
        $bootstrapClass = self::getBootstrapClass();
        $bootstrap = @new $bootstrapClass;

        $bootstrapReflection = new \ReflectionClass($bootstrapClass);
        $getTempDir = $bootstrapReflection->getMethod("getTempDir");
        $getTempDir->setAccessible(true);
        $tempDir = $getTempDir->invoke($bootstrap);

        $rdi = new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $rii = new \RecursiveIteratorIterator($rdi, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($rii as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()){
                @rmdir($item->getRealPath());
            } elseif (!in_array($item->getFilename(), $this->clearTempExcludedFiles())) {
                @unlink($item->getRealPath());
            }
        }
    }

    /**
     * @return string[]
     */
    protected function clearTempExcludedFiles()
    {
        return array(".gitignore");
    }

    /**
     * @param string $presenterName
     * @param string $actionName
     * @param array $params
     * @param array $post
     * @param array $files
     * @return IResponse
     */
    protected function runPresenter($presenterName, $actionName, array $params = array(), array $post = array(), array $files = array())
    {
        $presenterFactory = self::$container->getByType("Nette\\Application\\IPresenterFactory");
        /** @var Presenter $presenter */
        $presenter = $presenterFactory->createPresenter($presenterName);
        $presenter->autoCanonicalize = false;
        $params = array_merge($params, array("action" => $actionName));
        $request = new Request($presenterName, null, $params, $post, $files);
        return $presenter->run($request);
    }
}
