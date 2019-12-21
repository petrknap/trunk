<?php declare(strict_types=1);

namespace PetrKnapCz\Test;

use PetrKnapCz\BackupWatchdog;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class BackupWatchdogTest extends TestCase
{
    public function testIsRegistered()
    {
        $this->assertInstanceOf(
            BackupWatchdog::class,
            $this->get(BackupWatchdog::class)
        );
    }

    public function testCheckReturnsTrueAfterEarlyTouchOtherwiseFalse()
    {
        $id = 'test';
        $cache = new ArrayAdapter();
        $watchdog = new BackupWatchdog($cache, [$id => 1]);

        $this->assertFalse($watchdog->check($id));
        $watchdog->touch($id);
        $this->assertTrue($watchdog->check($id));

        sleep(2);
        $this->assertFalse($watchdog->check($id));
    }
}
