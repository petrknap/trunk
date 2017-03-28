<?php

namespace PetrKnap\Php\ServiceManager\Test\ServiceManagerTest;

use Exception;

class DefectiveService
{
    public function __construct()
    {
        throw new Exception("This is defect!");
    }

    public static function getClass()
    {
        return __CLASS__;
    }
}
