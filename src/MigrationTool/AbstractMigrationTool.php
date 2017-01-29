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

    const MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID = "Migration id for file '%s' is '%s'";
    const MESSAGE_FOUND_N_MIGRATION_FILES = "Found %d migration files";
    const MESSAGE_APPLYING_MIGRATION_FROM_FILE = "Applying '%s'";
    const MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION = "Detected gape before migration [id='%s']\nFiles to migrate:\n\t%s";

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
                        self::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION,
                        $this->getMigrationId($migrationFile),
                        implode("\n\t", $migrationFilesToMigrate)
                    );

                    if (null !== $this->getLogger()) {
                        $this->getLogger()->critical($message);
                    }

                    throw new MismatchException($message);
                }
            } else {
                $migrationFilesToMigrate[] = $migrationFile;
            }
        }

        foreach ($migrationFilesToMigrate as $migrationFile) {
            if (null !== $this->getLogger()) {
                $this->getLogger()->debug(
                    sprintf(
                        self::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                        $migrationFile
                    )
                );
            }

            $this->applyMigrationFile($migrationFile);
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
                }
            }
        }
        sort($migrationFiles);

        if (null !== $this->getLogger()) {
            $this->getLogger()->debug(
                sprintf(
                    self::MESSAGE_FOUND_N_MIGRATION_FILES,
                    count($migrationFiles)
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
        $migrationId =  $basenameParts[0];

        if (null !== $this->getLogger()) {
            $this->getLogger()->debug(
                sprintf(
                    self::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    $pathToMigrationFile,
                    $migrationId
                )
            );
        }

        return $migrationId;
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
