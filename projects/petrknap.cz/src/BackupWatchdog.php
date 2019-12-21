<?php declare(strict_types=1);

namespace PetrKnapCz;

use Psr\Cache\CacheItemPoolInterface;

class BackupWatchdog
{
    private $cache;
    private $lifeTimes;

    public function __construct(CacheItemPoolInterface $cache, array $lifeTimes)
    {
        $this->cache = $cache;
        $this->lifeTimes = $lifeTimes;
    }

    public function touch(string $backupId)
    {
        $item = $this->cache->getItem($this->checkAndConvertBackupId($backupId));
        $item->expiresAfter($this->lifeTimes[$backupId]);
        $this->cache->save($item);
    }

    public function check(string $backupId): bool
    {
        return $this->cache->hasItem($this->checkAndConvertBackupId($backupId));
    }

    private function checkAndConvertBackupId(string $backupId): string
    {
        if (!isset($this->lifeTimes[$backupId])) {
            throw new \Exception("Unknown backup \"{$backupId}\"");
        }
        return md5($backupId);
    }
}
