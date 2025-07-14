<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\BitrixCache;
use Phosagro\Iblocks;
use Phosagro\Object\QuestionTopic;

/**
 * @extends AbstractDirectory<QuestionTopic>
 */
final class QuestionTopicManager extends AbstractDirectory
{
    protected function createItem(array $row): void
    {
        $item = new QuestionTopic(
            (int) $row['ID'],
            trim((string) $row['NAME']),
        );

        $this->addItem($item->questionTopicIdentifier, $item);
    }

    protected function loadDatabase(BitrixCache $cache): array
    {
        /** @var array[] $result */
        $result = [];

        $found = \CIBlockElement::GetList(
            [
                'sort' => 'asc',
                'name' => 'asc',
                'id' => 'asc',
            ],
            [
                'IBLOCK_ID' => Iblocks::questionTopicId(),
            ],
            false,
            false,
            [
                'ID',
                'NAME',
            ],
        );

        while ($row = $found->Fetch()) {
            $result[] = $row;
        }

        $cache->tag(sprintf('iblock_id_%d', Iblocks::questionTopicId()));

        return $result;
    }
}
