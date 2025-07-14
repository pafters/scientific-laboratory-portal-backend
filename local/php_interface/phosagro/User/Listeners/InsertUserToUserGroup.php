<?php

declare(strict_types=1);

namespace Phosagro\User\Listeners;

use Bitrix\Main\EventManager;
use Phosagro\Enum\Edu;
use Phosagro\Enum\UserGroupType;
use Phosagro\Manager\Bitrix\UserFieldManager;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\GroupManager;
use Phosagro\Manager\PhosagroCompanyManager;
use Phosagro\System\ListenerInterface;
use Phosagro\Util\Text;

final class InsertUserToUserGroup implements ListenerInterface
{
    public function __construct(
        private readonly PhosagroCompanyManager $companies,
        private readonly UserFieldManager $fields,
        private readonly GroupManager $groups,
        private readonly UserManager $users,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('main', 'OnAfterUserAdd', $this->executeListener(...));
    }

    private function executeListener(array $fields): void
    {
        $result = ($fields['RESULT'] ?? null);

        if (false === $result) {
            return;
        }

        $user = $this->users->findById((int) $fields['ID']);

        if (null === $user) {
            return;
        }

        $edu = ($fields['UF_EDU_TYPE'] ?? null);
        $edu = filter_var($edu, FILTER_VALIDATE_INT);
        $edu = (\is_int($edu) ? $edu : null);
        $edu = ((null === $edu) ? null : $this->fields->getEnumById($edu));
        $edu = ((null === $edu) ? null : Edu::tryFrom(Text::lower($edu->code)));

        $company = ($fields['UF_PHOSAGRO_COMPANY'] ?? null);
        $company = filter_var($company, FILTER_VALIDATE_INT);
        $company = (\is_int($company) ? $company : null);
        $company = ((null === $company) ? null : $this->companies->findOne($company));

        if (null !== $company) {
            $groupType = UserGroupType::EMPLOYEES;
        } elseif (Edu::SCHOOL === $edu) {
            $groupType = UserGroupType::SCHOOLCHILDREN;
        } elseif (Edu::SUZ === $edu) {
            $groupType = UserGroupType::STUDENTS;
        } elseif (Edu::VUZ === $edu) {
            $groupType = UserGroupType::STUDENTS;
        } else {
            $groupType = null;
        }

        if (null === $groupType) {
            return;
        }

        $this->groups->linkUsers($user, $this->groups->findByType($groupType));
    }
}
