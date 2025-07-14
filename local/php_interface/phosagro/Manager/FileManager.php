<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Bitrix\Main\DB\Connection;
use Phosagro\Util\File;
use Phosagro\Iblocks;

final class FileManager
{
    private readonly string $uploadPath;

    public function __construct(
        private readonly Connection $database,
    ) {
        $this->uploadPath = $_SERVER['DOCUMENT_ROOT'] . \DIRECTORY_SEPARATOR . 'upload';
    }

    public function saveFile(string $content, string $name, string $temporary): int
    {
        $retryCount = 3;

        do {
            $temporaryName = sprintf('%s_%u', $temporary, mt_rand(0, 999999));
            $temporaryPath = $this->uploadPath . \DIRECTORY_SEPARATOR . $temporaryName;
            --$retryCount;
        } while (file_exists($temporaryPath) && ($retryCount > 0));

        if (file_exists($temporaryPath)) {
            throw new \RuntimeException(sprintf('Already exists temporary file "%s".', $temporaryPath));
        }

        File::write($temporaryPath, $content);

        $fields = \CFile::MakeFileArray($temporaryPath);

        if (!\is_array($fields)) {
            throw new \RuntimeException(sprintf('Can not read metadata for "%s".', $temporaryPath));
        }

        $identifier = \CFile::SaveFile($fields, 'task_completion', true);

        File::delete($temporaryPath);

        if (!\is_int($identifier) || ($identifier <= 0)) {
            throw new \RuntimeException(sprintf('Can not create database record for "%s".', $temporaryPath));
        }

        $this->database->queryExecute(
            sprintf(
                'update b_file set ORIGINAL_NAME = "%s" where ID = %d;',
                $this->database->getSqlHelper()->forSql($name),
                $identifier,
            )
        );

        return $identifier;
    }

    public function getFileByIdentifier($identifier): array
    {
        $result = $this->database->query("SELECT CONTENT_TYPE, ORIGINAL_NAME FROM b_file WHERE ID = " . $identifier);

        // Получаем данные из запроса
        $data = $result->fetchAll();

        // Выводим значения CONTENT_TYPE и ORIGINAL_NAME

        return $data;
    }
}
