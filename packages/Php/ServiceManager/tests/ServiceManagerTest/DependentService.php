<?php

namespace PetrKnap\Php\ServiceManager\Test\ServiceManagerTest;

class DependentService
{
    /**
     * @var IndependentService
     */
    private $independentService;

    public function __construct(IndependentService $independentService)
    {
        $this->independentService = $independentService;
    }

    public static function getClass()
    {
        return __CLASS__;
    }
}
