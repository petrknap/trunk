<?php

namespace PetrKnap\Nette\Bootstrap;

use Nette;

class Configurator extends Nette\Configurator
{
    /**
     * @var array
     */
    private $defaultParameters;

    public function __construct(array $defaultParameters = array())
    {
        $this->defaultParameters = $defaultParameters;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultParameters()
    {
        return array_merge(parent::getDefaultParameters(), $this->defaultParameters);
    }
}
