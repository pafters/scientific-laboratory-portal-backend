<?php

declare(strict_types=1);

namespace Phosagro\User\Agents;

use Bitrix\Main\UserTable;
use Phosagro\Site\SiteInfo;
use Phosagro\System\AgentInterface;
use Phosagro\System\UrlManager;

final class NotifyAdminWhenUserRegistered implements AgentInterface
{
    public function __construct(
        private readonly \CUser $bitrixUser,
        private readonly SiteInfo $siteInfo,
        private readonly UrlManager $urlManager,
    ) {}

    public function execute(): void
    {
        /** @var array<int,int> $userIdIndex */
        $userIdIndex = [];

        $found = UserTable::getList([
            'filter' => [
                '=ACTIVE' => 'N',
                '=CONFIRM_CODE' => '',
                '=PHONE_AUTH.CONFIRMED' => 'Y',
                '=UF_STOP_REG_ADM_NOTIFY' => '0',
            ],
            'limit' => 100,
            'order' => [
                'ID' => 'ASC',
            ],
            'select' => [
                'ID',
            ],
        ]);

        while ($row = $found->fetchRaw()) {
            $userId = (int) $row['ID'];
            $userIdIndex[$userId] = $userId;
        }

        foreach ($userIdIndex as $userId) {
            $adminUrl = sprintf('/bitrix/admin/user_edit.php?lang=ru&ID=%d', $userId);
            $emailResult = \CEvent::Send('NEW_USER_MODERATION', 's1', [
                'ADMIN_URL' => $this->urlManager->makeAbsolute($adminUrl),
                'EMAIL_TO' => $this->siteInfo->getAdminEmail(),
                'SERVER_NAME' => $this->siteInfo->getSiteName(),
                // 'DEFAULT_EMAIL_FROM' => 'подставляется битриксом автоматически',
            ]);

            if (!$emailResult) {
                throw new \RuntimeException('Can not send email.');
            }

            $uodateResult = $this->bitrixUser->Update($userId, [
                'UF_STOP_REG_ADM_NOTIFY' => '1',
            ]);

            if (!$uodateResult) {
                throw new \RuntimeException('Can update user. '.$this->bitrixUser->LAST_ERROR);
            }
        }
    }
}
