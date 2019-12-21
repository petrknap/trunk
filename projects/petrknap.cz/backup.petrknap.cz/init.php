<?php declare(strict_types=1);

namespace PetrKnapCz;

require_once __DIR__ . '/../vendor/autoload.php';

return container()->get(BackupWatchdog::class);
