<?php

namespace PetrKnap\Nette\Bootstrap\Test\PhpUnit\NetteTestCaseTest;

use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;

class TestPresenter extends Presenter
{
    public function actionTest()
    {
        $request = $this->getRequest();
        $this->sendResponse(new JsonResponse(array(
            "parameters" => $request->getParameters(),
            "post" => $request->getPost(),
            "files" => $request->getFiles()
        )));
    }
}
