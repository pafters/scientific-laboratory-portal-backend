<?php

declare(strict_types=1);

namespace Phosagro\User\Listeners;

use Bitrix\Main\DB\Connection;
use Bitrix\Main\EventManager;
use Phosagro\System\Array\Accessor;
use Phosagro\System\Clock;
use Phosagro\System\ListenerInterface;
use Phosagro\User\AuthorizationContext;

final class LogSessionLength implements ListenerInterface
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly \CUser $bitrixUser,
        private readonly Clock $clock,
        private readonly Connection $database,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('main', 'OnBeforeProlog', $this->executeListener(...));
    }

    private function executeListener(): void
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            return;
        }

        $row = $this->database->query(sprintf(
            <<<'SQL'
            select ID, TIMESTAMP_X
            from b_event_log
            where AUDIT_TYPE_ID = 'USER_AUTHORIZE'
              and ITEM_ID = %d
            order by TIMESTAMP_X desc
            limit 1
            SQL,
            $user->userIdentifier,
        ))->fetchRaw();

        if (!$row) {
            return;
        }

        $accessor = new Accessor($row);
        $length = $this->clock->now()->getTimestamp() - $accessor->getDate('TIMESTAMP_X')->getTimestamp();

        $this->database->queryExecute(sprintf(
            <<<'SQL'
            update b_event_log
            set DESCRIPTION = '%s'
            where ID = %d
            SQL,
            $this->database->getSqlHelper()->forSql(sprintf('Длительность сессии %d с.', $length)),
            $row['ID'],
        ));
    }
}
