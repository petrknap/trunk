<?php

namespace PetrKnap\Test\Php\ServiceManager\ServiceManagerTest;

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
