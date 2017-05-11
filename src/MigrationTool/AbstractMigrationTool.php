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
    const MESSAGE__FOUND_UNSUPPORTED_FILE__PATH = "Found unsupported file {path}";
    const MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN = "Found {count} migration files in {path} matching {pattern}";
    const MESSAGE__MIGRATION_FILE_APPLIED__PATH = "Migration file {path} applied";
    const MESSAGE__THERE_IS_NOTHING_MATCHING_PATTERN__PATH_PATTERN = "In {path} is nothing matching {pattern}";
    const MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN = "In {path} is nothing matching {pattern} to migrate";
    const MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID = "Detected gape before migration {id}";
    const MESSAGE__DONE = "Database is now up-to-date";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Interpolates context values into the message placeholders for exceptions
     *
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function interpolate($message, array $context = array())
    {
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }

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
                    $context = array(
                        "id" => $this->getMigrationId($migrationFile)
                    );

                    if ($this->getLogger()) {
                        $this->getLogger()->critical(
                            self::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                            $context
                        );
                    }

                    throw new MismatchException(
                        sprintf(
                            "%s\nFiles to migrate:\n\t%s",
                            $this->interpolate(
                                self::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                                $context
                            ),
                            implode("\n\t", $migrationFilesToMigrate)
                        )
                    );
                }
            } else {
                $migrationFilesToMigrate[] = $migrationFile;
            }
        }

        if (empty($migrationFilesToMigrate)) {
            $context = array(
                "path" => $this->getPathToDirectoryWithMigrationFiles(),
                "pattern" => $this->getMigrationFilePattern(),
            );

            if ($this->getLogger()) {
                $this->getLogger()->notice(
                    self::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN,
                    $context
                );
            } else {
                user_error(
                    $this->interpolate(
                        self::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN,
                        $context
                    ),
                    E_USER_NOTICE
                );
            }
        } else {
            foreach ($migrationFilesToMigrate as $migrationFile) {
                $this->applyMigrationFile($migrationFile);

                if ($this->getLogger()) {
                    $this->getLogger()->info(
                        self::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                        array(
                            "path" => $migrationFile,
                        )
                    );
                }
            }
        }

        if ($this->getLogger()) {
            $this->getLogger()->info(
                self::MESSAGE__DONE
            );
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
                if (preg_match($this->getMigrationFilePattern(), $fileInfo->getRealPath())) {
                    $migrationFiles[] = $fileInfo->getRealPath();
                } else {
                    $context = array(
                        "path" => $fileInfo->getRealPath(),
                    );

                    if ($this->getLogger()) {
                        $this->getLogger()->notice(
                            self::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                            $context
                        );
                    } else {
                        user_error(
                            $this->interpolate(
                                self::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                                $context
                            ),
                            E_USER_NOTICE
                        );
                    }
                }
            }
        }
        sort($migrationFiles);

        if (empty($migrationFiles)) {
            $context = array(
                "path" => $this->getPathToDirectoryWithMigrationFiles(),
                "pattern" => $this->getMigrationFilePattern(),
            );

            if ($this->getLogger()) {
                $this->getLogger()->warning(
                    self::MESSAGE__THERE_IS_NOTHING_MATCHING_PATTERN__PATH_PATTERN,
                    $context
                );
            } else {
                user_error(
                    $this->interpolate(
                        self::MESSAGE__THERE_IS_NOTHING_MATCHING_PATTERN__PATH_PATTERN,
                        $context
                    ),
                    E_USER_WARNING
                );
            }
        }

        if ($this->getLogger()) {
            $this->getLogger()->info(
                self::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                array(
                    "count" => count($migrationFiles),
                    "path" => $this->getPathToDirectoryWithMigrationFiles(),
                    "pattern" => $this->getMigrationFilePattern(),
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
     * @return string
     */
    protected function getMigrationFilePattern()
    {
        return '/^.*$/i';
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
