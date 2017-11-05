<?php

namespace App\Test;

use PetrKnap\Php\MigrationTool\SqlMigrationTool;
use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class AppTestCase extends TestCase
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

require_once __DIR__ . '/mocks.php';
