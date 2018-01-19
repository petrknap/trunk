<?php

namespace PetrKnapCz;

use Swift_Mailer;
use Swift_Message;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpParamsInspection */
authorize(container()->get(Request::class));

/** @var BackUpService $backUpService */
$backUpService = container()->get(BackUpService::class);
$changedFiles = iterator_to_array($backUpService->getChangedFiles());
$backUpService->backUp();

if (!empty($changedFiles)) {
    $message = new Swift_Message(
        'Backup completed',
        'Backed up files:' . PHP_EOL . implode(PHP_EOL, $changedFiles)
    );

    $message->setFrom(container()->getParameter('email.no_reply_address'));
    $message->addTo(container()->getParameter('email.main_address'));

    container()->get(Swift_Mailer::class)->send($message);
}

done();
