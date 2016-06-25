<?php

namespace PetrKnap\Php\MigrationTool;

use PetrKnap\Php\MigrationTool\Exception\MismatchException;

/**
 * Abstract migration tool
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-06-22
 * @license  https://github.com/petrknap/php-migrationtool/blob/master/LICENSE MIT
 */
abstract class AbstractMigrationTool implements MigrationToolInterface
{
    const MIGRATION_FILE_PATTERN = '/^.*$/i';

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
                    throw new MismatchException(
                        sprintf(
                            "Detected gape before applied migration [id='%s']",
                            $this->getMigrationId($migrationFile)
                        )
                    );
                }
            } else {
                $migrationFilesToMigrate[] = $migrationFile;
            }
        }

        foreach ($migrationFilesToMigrate as $migrationFile) {
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
