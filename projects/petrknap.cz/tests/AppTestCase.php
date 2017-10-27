<?php

namespace App\Test;

use PetrKnap\Php\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class AppTestCase extends TestCase
{
    protected function get($id)
    {
        return ServiceManager::getInstance()->get($id);
    }
}

require_once __DIR__ . '/mocks.php';
