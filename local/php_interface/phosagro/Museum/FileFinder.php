<?php

declare(strict_types=1);

namespace Phosagro\Museum;

final class FileFinder
{
    /** @var \SplFileInfo[] */
    private ?array $databaseFiles = null;

    public function __construct(
        private readonly MuseumConfig $config,
    ) {}

    /**
     * @return \SplFileInfo[]
     */
    public function findDatabaseFiles(): array
    {
        if (null !== $this->databaseFiles) {
            return $this->databaseFiles;
        }

        $this->databaseFiles = [];

        $directory = $this->config->getDatabaseFolderPath();

        try {
            foreach (new \FilesystemIterator($directory) as $item) {
                $this->databaseFiles[] = $item;
            }

            usort($this->databaseFiles, static function (\SplFileInfo $first, \SplFileInfo $second): int {
                $firstTime = $first->getMTime();

                if (false === $firstTime) {
                    throw new \RuntimeException(sprintf(
                        'Can not get modification time of "%s".',
                        $first->getPathname(),
                    ));
                }

                $secondTime = $second->getMTime();

                if (false === $secondTime) {
                    throw new \RuntimeException(sprintf(
                        'Can not get modification time of "%s".',
                        $second->getPathname(),
                    ));
                }

                return $firstTime <=> $secondTime;
            });

            return $this->databaseFiles;
        } catch (\Throwable $configError) {
            throw new MuseumException(GetMessage('MUSEUM_DATABASE_WRONG_CONFIG', [
                '#ERROR_CODE#' => $configError->getCode(),
                '#ERROR_MESSAGE#' => $configError->getMessage(),
            ]), $directory, $configError);
        }
    }

    public function findLastDatabaseFile(): ?\SplFileInfo
    {
        $found = $this->findDatabaseFiles();

        if ([] === $found) {
            return null;
        }

        return $found[array_key_last($found)];
    }

    /**
     * @return \SplFileInfo[]
     */
    public function findOldDatabaseFiles(): array
    {
        $found = $this->findDatabaseFiles();

        if (\count($found) <= 1) {
            return [];
        }

        return \array_slice($found, 0, \count($found) - 1);
    }
}
