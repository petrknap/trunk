<?php

namespace PetrKnapCz;

use PetrKnap\Php\MigrationTool\SqlMigrationTool;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpParamsInspection */
authorize(container()->get(Request::class));

container()->get(SqlMigrationTool::class)
    ->migrate();

done();
