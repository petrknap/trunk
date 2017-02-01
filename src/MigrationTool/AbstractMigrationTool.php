<?php

namespace PetrKnap\Php\MigrationTool;

use PetrKnap\Php\MigrationTool\Exception\MismatchException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Abstract migration tool
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-06-22
 * @license  https://github.com/petrknap/php-migrationtool/blob/master/LICENSE MIT
 */
abstract class AbstractMigrationTool implements MigrationToolInterface, LoggerAwareInterface
{
    const MIGRATION_FILE_PATTERN = '/^.*$/i';

    const MESSAGE_FOUND_UNSUPPORTED_FILE_PATH = "Found unsupported file [path='%s']";
    const MESSAGE_FOUND_MIGRATION_FILES_COUNT_PATH_PATTERN = "Found migration files [count=%d, path='%s', pattern='%s']";
    const MESSAGE_MIGRATION_FILE_APPLIED_PATH = "Migration file applied [path='%s']";
    const MESSAGE_THERE_IS_NOTHING_TO_MIGRATE_PATH_PATTERN = "There is nothing to migrate [path='%s', pattern='%s']";
    const MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION_ID = "Detected gape before migration [id='%s']\nFiles to migrate:\n\t%s";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @inheritdoc
     */
    public function migrate()
    {
        $migrationFiles = $this->getMigrationFiles();
        $migrationFilesToMigrate = array();
        foreach ($migrationFiles as $migrationFile) {
            if ($this->isMigrationApplied($migrationFile)) {
                if (!empty($migrationFilesToMigrate)) {
                    $message = sprintf(
                        self::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION_ID,
                        $this->getMigrationId($migrationFile),
                        implode("\n\t", $migrationFilesToMigrate)
                    );

                    if ($this->getLogger()) {
                        $this->getLogger()->critical($message);
                    }

                    throw new MismatchException($message);
                }
            } else {
                $migrationFilesToMigrate[] = $migrationFile;
            }
        }

        if (empty($migrationFilesToMigrate)) {
            if ($this->getLogger()) {
                $this->getLogger()->notice(
                    sprintf(
                        self::MESSAGE_THERE_IS_NOTHING_TO_MIGRATE_PATH_PATTERN,
                        $this->getPathToDirectoryWithMigrationFiles(),
                        static::MIGRATION_FILE_PATTERN
                    )
                );
            }
        } else {
            foreach ($migrationFilesToMigrate as $migrationFile) {
                $this->applyMigrationFile($migrationFile);

                if ($this->getLogger()) {
                    $this->getLogger()->info(
                        sprintf(
                            self::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                            $migrationFile
                        )
                    );
                }
            }
        }
    }

    /**
     * Returns list of paths to migration files
     *
     * @return string[]
     */
    protected function getMigrationFiles()
    {
        $directoryIterator = new \DirectoryIterator($this->getPathToDirectoryWithMigrationFiles());
        $migrationFiles = array();
        foreach ($directoryIterator as $fileInfo) {
            /** @var \SplFileInfo $fileInfo */
            if ($fileInfo->isFile()) {
                if (preg_match(static::MIGRATION_FILE_PATTERN, $fileInfo->getRealPath())) {
                    $migrationFiles[] = $fileInfo->getRealPath();
                } elseif ($this->getLogger()) {
                    $this->getLogger()->notice(
                        sprintf(
                            self::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                            $fileInfo->getRealPath()
                        )
                    );
                }
            }
        }
        sort($migrationFiles);

        if ($this->getLogger()) {
            $this->getLogger()->info(
                sprintf(
                    self::MESSAGE_FOUND_MIGRATION_FILES_COUNT_PATH_PATTERN,
                    count($migrationFiles),
                    $this->getPathToDirectoryWithMigrationFiles(),
                    static::MIGRATION_FILE_PATTERN
                )
            );
        }

        return $migrationFiles;
    }

    /**
     * @param string $pathToMigrationFile
     * @return string
     */
    protected function getMigrationId($pathToMigrationFile)
    {
        $fileInfo = new \SplFileInfo($pathToMigrationFile);
        $basenameParts = explode(" ", $fileInfo->getBasename(".{$fileInfo->getExtension()}"));
        return $basenameParts[0];
    }

    /**
     * @param string $pathToMigrationFile
     * @return bool
     */
    abstract protected function isMigrationApplied($pathToMigrationFile);

    /**
     * @param $pathToMigrationFile
     * @return void
     */
    abstract protected function applyMigrationFile($pathToMigrationFile);

    /**
     * @return string
     */
    abstract protected function getPathToDirectoryWithMigrationFiles();
}
