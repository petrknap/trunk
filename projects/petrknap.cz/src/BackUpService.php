<?php

namespace PetrKnapCz;

class BackUpService
{
    /**
     * @var string
     */
    private $backUpDir;

    /**
     * @var array
     */
    private $backedUpFiles;

    public function __construct(string $backUpDir, array $backedUpFiles)
    {
        $this->backedUpFiles = $backedUpFiles;
        $this->backUpDir = $backUpDir;
    }

    /**
     * @internal public for test purpose only
     * @param string $sourcePath
     * @return string
     */
    public function getBackUpPath(string $sourcePath): string
    {
        $fileName = str_replace(['_', ':', DIRECTORY_SEPARATOR], ['__', '', '_'], $sourcePath);

        return $this->backUpDir . DIRECTORY_SEPARATOR . $fileName;
    }

    public function getChangedFiles(): \Generator
    {
        foreach ($this->backedUpFiles as $backedUpFile) {
            if (file_exists($backedUpFile) && file_exists($this->getBackUpPath($backedUpFile)) && sha1_file($backedUpFile) === sha1_file($this->getBackUpPath($backedUpFile))) {
                continue;
            }
            yield $backedUpFile;
        }
    }

    public function backUp()
    {
        foreach ($this->getChangedFiles() as $changedFile) {
            $backedUpFile = $this->getBackUpPath($changedFile);
            if (file_exists($backedUpFile)) {
                copy($backedUpFile, $backedUpFile . '.prev');
            }
            copy($changedFile, $backedUpFile);
        }
    }
}
