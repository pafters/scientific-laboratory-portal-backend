<?php

declare(strict_types=1);

use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;

return static function (): void {
    /** @var int[] $messageIdList */
    $messageIdList = [];

    $foundMessage = EventMessageTable::getList([
        'filter' => [
            '=EVENT_NAME' => 'NEW_USER',
        ],
        'order' => [
            'ID' => 'DESC',
        ],
        'select' => [
            'ID',
        ],
    ]);

    while ($rowMessage = $foundMessage->fetchRaw()) {
        $messageIdList[] = (int) $rowMessage['ID'];
    }

    foreach ($messageIdList as $messageId) {
        $messageResult = EventMessageTable::delete($messageId);

        if (!$messageResult->isSuccess()) {
            $messageError = implode(' ', $messageResult->getErrorMessages());

            throw new RuntimeException('Can not delete message. '.$messageError);
        }
    }

    /** @var int[] $eventIdList */
    $eventIdList = [];

    $foundEvent = EventTypeTable::getList([
        'filter' => [
            '=EVENT_NAME' => 'NEW_USER',
        ],
        'order' => [
            'ID' => 'DESC',
        ],
        'select' => [
            'ID',
        ],
    ]);

    while ($rowEvent = $foundEvent->fetchRaw()) {
        $eventIdList[] = (int) $rowEvent['ID'];
    }

    foreach ($eventIdList as $eventId) {
        $eventResult = EventTypeTable::delete($eventId);

        if (!$eventResult->isSuccess()) {
            $eventError = implode(' ', $eventResult->getErrorMessages());

            throw new RuntimeException('Can not delete event. '.$eventError);
        }
    }
};
