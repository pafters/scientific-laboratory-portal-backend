<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\ORM\Fields\ExpressionField;

final class EmailHelper
{
    public function __construct(
        private readonly DatabaseHelper $database,
    ) {}

    public function changeEmailMessage(string $type, string $message, array $fields): void
    {
        $this->database->assertSuccess(EventMessageTable::update(
            $this->getEventMessageId($type, $message),
            $fields,
        ), 'event message', "{$type}/{$message}", 'change');
    }

    public function createEmailEventMessage(string $type, string $message, array $fields = []): void
    {
        $this->database->assertSuccess(EventMessageTable::add(array_replace_recursive([
            'ACTIVE' => 'Y',
            'BCC' => '',
            'BODY_TYPE' => 'text',
            'CC' => '',
            'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
            'EMAIL_TO' => '#EMAIL_TO#',
            'EVENT_NAME' => $type,
            'FIELD1_NAME' => '',
            'FIELD1_VALUE' => '',
            'FIELD2_NAME' => '',
            'FIELD2_VALUE' => '',
            'IN_REPLY_TO' => '',
            'LANGUAGE_ID' => '',
            'LID' => 's1',
            'MESSAGE' => '',
            'MESSAGE_PHP' => '',
            'PRIORITY' => '',
            'REPLY_TO' => '',
            'SITE_TEMPLATE_ID' => '',
            'SUBJECT' => $message,
        ], $fields)), 'event message', "{$type}/{$message}", 'create');

        $this->database->assertSuccess(EventMessageSiteTable::add([
            'EVENT_MESSAGE_ID' => $this->getEventMessageId($type, $message),
            'SITE_ID' => 's1',
        ]), 'event message site', "{$type}/{$message}", 'create');
    }

    public function createEmailEventType(string $type, array $fields = []): void
    {
        $this->database->assertSuccess(EventTypeTable::add(array_replace_recursive([
            'DESCRIPTION' => '',
            'EVENT_NAME' => $type,
            'EVENT_TYPE' => EventTypeTable::TYPE_EMAIL,
            'LID' => 'ru',
            'NAME' => '',
            'SORT' => $this->getNextSort(),
        ], $fields)), 'event type', $type, 'create');
    }

    public function getEventMessageId(string $type, string $message): int
    {
        return $this->database->fetchSingleId(EventMessageTable::getList([
            'filter' => [
                '=EVENT_NAME' => $type,
                '=SUBJECT' => $message,
            ],
            'limit' => 2,
            'select' => [
                'ID',
            ],
        ]), 'event message', "{$type}/{$message}");
    }

    private function getNextSort(): int
    {
        return $this->database->fetchSingleInt(EventTypeTable::getList([
            'select' => [
                new ExpressionField('MAX_SORT', 'max(SORT)'),
            ],
        ]), 'event type', '', 'MAX_SORT') + 10;
    }
}
