<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Object\Faq;
use Phosagro\Util\Text;

/**
 * @extends AbstractIblockElementManager<Faq>
 */
final class FaqManager extends AbstractIblockElementManager
{
    protected function createFromBitrixData(array $row): object
    {
        return new Faq(
            Text::bitrix(trim((string) $row['DETAIL_TEXT']), trim((string) $row['DETAIL_TEXT_TYPE'])),
            (int) $row['ID'],
            trim((string) $row['NAME']),
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'DETAIL_TEXT',
            'DETAIL_TEXT_TYPE',
            'ID',
            'NAME',
        ];
    }

    protected function getDefaultOrder(): array
    {
        return [
            'sort' => 'asc',
            'id' => 'asc',
        ];
    }
}
