<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Highloadblock\HighloadBlockLangTable;
use Bitrix\Highloadblock\HighloadBlockTable;
use Phosagro\Util\Text;

final class HighloadblockHelper
{
    public function __construct(
        private readonly DatabaseHelper $databaseHelper,
    ) {}

    public function createHighloadblock(string $name, string $nameEn = '', string $nameRu = ''): void
    {
        $result = HighloadBlockTable::add([
            'NAME' => $name,
            'TABLE_NAME' => 'phosagro_'.Text::snake($name),
        ]);

        if (!$result->isSuccess()) {
            $error = implode(' ', $result->getErrorMessages());

            throw new \RuntimeException('Can not create highloadblock. '.$error);
        }

        if ('' !== $nameEn) {
            $this->databaseHelper->assertSuccess(
                HighloadBlockLangTable::add([
                    'ID' => $result->getId(),
                    'LID' => 'en',
                    'NAME' => $nameEn,
                ]),
                'highloadblock lang',
                "{$name}/en",
                'create',
            );
        }

        if ('' !== $nameRu) {
            $this->databaseHelper->assertSuccess(
                HighloadBlockLangTable::add([
                    'ID' => $result->getId(),
                    'LID' => 'ru',
                    'NAME' => $nameRu,
                ]),
                'highloadblock lang',
                "{$name}/ru",
                'create',
            );
        }
    }

    public function deleteHighloadblock(string $highloadblock): void
    {
        $result = HighloadBlockTable::delete($this->getHighloadblockId($highloadblock));
        $this->databaseHelper->assertSuccess($result, 'highloadblock', $highloadblock, 'delete');
    }

    public function getHighloadblockId(string $highloadblock): int
    {
        $found = HighloadBlockTable::getList([
            'filter' => [
                '=NAME' => $highloadblock,
            ],
            'limit' => 2,
            'select' => [
                'ID',
            ],
        ]);

        $single = $found->fetchRaw();

        if (false === $single) {
            throw new \RuntimeException(sprintf('Not found highloadblock "%s".', $highloadblock));
        }

        if (false !== $found->fetchRaw()) {
            throw new \RuntimeException(sprintf('Found more than one highloadblock "%s".', $highloadblock));
        }

        return (int) $single['ID'];
    }
}
