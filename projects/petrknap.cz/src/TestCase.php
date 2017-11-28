<?php

namespace PetrKnapCz {

    use PetrKnap\Php\MigrationTool\SqlMigrationTool;
    use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
    use PetrKnap\Php\ServiceManager\ServiceManager;
    use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

    abstract class TestCase extends PHPUnit_TestCase
    {
        protected function get($id)
        {
            return ServiceManager::getInstance()->get($id);
        }

        /**
         * @var \PDO|null
         */
        private static $pdo = null;

        /**
         * @var bool
         */
        private static $initializedServiceManager = false;

        public static function setUpBeforeClass()
        {
            if (!self::$initializedServiceManager) {
                $config = new ConfigurationBuilder();
                $config->addFactory(\PDO::class, function () {
                    return self::$pdo;
                });
                $config->setShared(\PDO::class, false);

                @ServiceManager::addConfig($config);

                self::$initializedServiceManager = true;
            }
        }

        public function setUp()
        {
            self::$pdo = new \PDO('sqlite::memory:');
            $this->get(SqlMigrationTool::class)->migrate();
        }
    }
}

namespace Symfony\Component\HttpFoundation {
    function time()
    {
        return 0;
    }
}
