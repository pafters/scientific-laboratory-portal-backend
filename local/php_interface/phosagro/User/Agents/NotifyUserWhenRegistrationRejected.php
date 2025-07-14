<?php

declare(strict_types=1);

namespace Phosagro\User\Agents;

use Bitrix\Main\UserTable;
use Phosagro\Site\SiteInfo;
use Phosagro\System\AgentInterface;
use Phosagro\System\Array\Accessor;
use Phosagro\System\UrlManager;

final class NotifyUserWhenRegistrationRejected implements AgentInterface
{
    public function __construct(
        private readonly \CUser $bitrixUser,
        private readonly SiteInfo $siteInfo,
        private readonly UrlManager $urlManager,
    ) {}

    public function execute(): void
    {
        /** @var array<int,string> $userEmailIndex */
        $userEmailIndex = [];

        $found = UserTable::getList([
            'filter' => [
                '=BLOCKED' => 'Y',
                '=UF_STOP_REG_USER_REJECT' => '0',
            ],
            'limit' => 100,
            'order' => [
                'ID' => 'ASC',
            ],
            'select' => [
                'EMAIL',
                'ID',
            ],
        ]);

        while ($row = $found->fetchRaw()) {
            $accessor = new Accessor($row);
            $userEmailIndex[$accessor->getIntParsed('ID')] = $accessor->getStringFilled('EMAIL');
        }

        foreach ($userEmailIndex as $userId => $userEmail) {
            $result = \CEvent::Send('NEW_USER_REJECTION', 's1', [
                'EMAIL_TO' => $userEmail,
                'OBJECTION_EMAIL' => $this->siteInfo->getAdminEmail(),
                'SERVER_NAME' => $this->siteInfo->getSiteName(),
                // 'DEFAULT_EMAIL_FROM' => 'подставляется битриксом автоматически',
            ]);

            if (!$result) {
                throw new \RuntimeException('Can not send email.');
            }

            $uodateResult = $this->bitrixUser->Update($userId, [
                'UF_STOP_REG_USER_REJECT' => '1',
            ]);

            if (!$uodateResult) {
                throw new \RuntimeException('Can update user. '.$this->bitrixUser->LAST_ERROR);
            }
        }
    }
}
