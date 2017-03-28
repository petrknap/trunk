<?php

namespace PetrKnap\Nette\Bootstrap\Test\PhpUnit\NetteTestCaseTest;

use PetrKnap\Nette;

class NetteTestCase extends Nette\Bootstrap\PhpUnit\NetteTestCase
{
    const NETTE_BOOTSTRAP_CLASS = "PetrKnap\\Nette\\Bootstrap\\Test\\PhpUnit\\NetteTestCaseTest\\Bootstrap";

    /**
     * @inheritdoc
     */
    public function getContainer()
    {
        return parent::getContainer();
    }
}
