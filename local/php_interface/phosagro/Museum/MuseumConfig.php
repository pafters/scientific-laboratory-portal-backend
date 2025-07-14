<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Bitrix\Main\Config\Configuration;

final class MuseumConfig
{
    private const OPTION_FOLDER = 'museum_database_folder';

    /**
     * @throws MuseumException
     */
    public function getDatabaseFolderPath(): string
    {
        $path = Configuration::getValue(self::OPTION_FOLDER);

        if (!\is_string($path)) {
            throw new MuseumException(sprintf(
                'Not configured folder for uploading museum database, "%s" in "bitrix/.settings.php".',
                self::OPTION_FOLDER,
            ), self::OPTION_FOLDER);
        }

        $path = trim($path);

        if ('' === $path) {
            throw new MuseumException(sprintf(
                'Not configured folder for uploading museum database, "%s" in "bitrix/.settings.php".',
                self::OPTION_FOLDER,
            ), self::OPTION_FOLDER);
        }

        return $path;
    }
}
