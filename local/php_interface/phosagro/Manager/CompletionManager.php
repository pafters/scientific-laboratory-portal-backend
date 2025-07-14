<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Object\Completion;

/**
 * @extends AbstractIblockElementManager<Completion>
 */
final class CompletionManager extends AbstractIblockElementManager
{
    protected function createFromBitrixData(array $row): object
    {
        $filesRejected = false;

        foreach ((array) $row['PROPERTY_FILES_DESCRIPTION'] as $description) {
            if (\is_string($description) && ('' !== trim($description))) {
                $filesRejected = true;
            }
        }

        return new Completion(
            (int) $row['PROPERTY_ANSWER_VALUE'],
            'Y' === $row['ACTIVE'],
            (int) $row['ID'],
            $filesRejected,
            (int) $row['PROPERTY_PARTICIPANT_VALUE'],
            (int) $row['PROPERTY_TASK_VALUE'],
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'ACTIVE',
            'ID',
            'PROPERTY_ANSWER',
            'PROPERTY_FILES',
            'PROPERTY_PARTICIPANT',
            'PROPERTY_TASK',
        ];
    }
}
