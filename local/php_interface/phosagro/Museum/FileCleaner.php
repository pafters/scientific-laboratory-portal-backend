<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Phosagro\Util\File;

final class FileCleaner
{
    public function __construct(
        private readonly FileFinder $files,
    ) {}

    public function cleanFiles(): void
    {
        foreach ($this->files->findOldDatabaseFiles() as $file) {
            File::delete($file->getPathname());
        }
    }
}
