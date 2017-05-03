<?php

namespace PetrKnap\Nette\Bootstrap\Test\BootstrapTest;

use PetrKnap\Nette\Bootstrap\Bootstrap as B;

class Bootstrap extends B
{
    /**
     * @inheritdoc
     */
    public function getDebugMode()
    {
        return self::getOption(self::OPTION_IS_TEST_RUN) === true;
    }

    /**
     * @inheritdoc
     */
    public function getAppDir()
    {
        return __DIR__;
    }

    /**
     * @inheritdoc
     */
    public function getLogDir()
    {
        return __DIR__ . "/log";
    }

    /**
     * @inheritdoc
     */
    public function getTempDir()
    {
        return __DIR__ . "/tmp";
    }

    /**
     * @inheritdoc
     */
    public function getConfigFiles()
    {
        return array(
            __DIR__ . "/cfg/first.neon",
            __DIR__ . "/cfg/second.neon"
        );
    }

    /**
     * @inheritdoc
     */
    public static function getOptions()
    {
        return parent::getOptions();
    }
}
