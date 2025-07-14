<?php

declare(strict_types=1);

namespace Phosagro\Manager\Bitrix;

use Bitrix\Main\UserTable;
use Phosagro\Enum\Edu;
use Phosagro\Enum\Gender;
use Phosagro\Manager\CityManager;
use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\PhosagroCompanyManager;
use Phosagro\Object\Bitrix\User;
use Phosagro\Permissions\GroupIdentifiers;
use Phosagro\System\Array\Accessor;
use Phosagro\System\Array\AccessorException;
use Phosagro\System\Array\NullRequiredException;
use Phosagro\System\Clock;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;
use Phosagro\Util\Text;

final class UserManager
{
    /** @var array<string,false|User> */
    private array $byEmail = [];

    /** @var array<string,false|User> */
    private array $byGroup = [];

    /** @var array<int,false|User> */
    private array $byId = [];

    /** @var array<string,false|User> */
    private array $byLogin = [];

    /** @var array<string,false|User> */
    private array $byPhone = [];

    /** @var \WeakMap<User,int> */
    private readonly \WeakMap $idMap;

    public function __construct(
        private readonly CityManager $cityManager,
        private readonly Clock $clock,
        private readonly PhosagroCompanyManager $phosagroCompanyManager,
        private readonly UserFieldManager $userFieldManager,
    ) {
        $this->idMap = new \WeakMap();
    }

    /**
     * @return User[]
     */
    public function findActiveUsers(): array
    {
        return $this->loadUser([
            'filter' => [
                '=ACTIVE' => 'Y',
                '=BLOCKED' => 'N',
            ],
            'order' => [
                'ID' => 'ASC',
            ],
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findCached(['filter' => ['=EMAIL' => $email]], "~{$email}", $this->byEmail);
    }

    public function findById(int $id): ?User
    {
        return $this->findCached(['filter' => ['=ID' => $id]], sprintf('%d', $id), $this->byId);
    }

    public function findByLogin(string $login): ?User
    {
        return $this->findCached(['filter' => ['=LOGIN' => $login]], "~{$login}", $this->byLogin);
    }

    public function findByPhone(string $phone): ?User
    {
        return $this->findCached(['filter' => ['=PHONE_AUTH.PHONE_NUMBER' => $phone]], "~{$phone}", $this->byPhone);
    }

    public function findPublicModerator(): ?User
    {
        $now = $this->clock->now();

        return $this->findCached([
            'filter' => [
                '=ACTIVE' => 'Y',
                '=BLOCKED' => 'N',
                '=GROUPS.GROUP_ID' => GroupIdentifiers::PUBLIC_MODERATOR->value,
                [
                    'LOGIC' => 'OR',
                    'GROUPS.DATE_ACTIVE_FROM' => null,
                    '<GROUPS.DATE_ACTIVE_FROM' => Date::toFormat($now, DateFormat::BITRIX),
                ],
                [
                    'LOGIC' => 'OR',
                    '=GROUPS.DATE_ACTIVE_TO' => null,
                    '>GROUPS.DATE_ACTIVE_TO' => Date::toFormat($now, DateFormat::BITRIX),
                ],
            ],
            'limit' => 1,
            'order' => ['ID' => 'ASC'],
        ], GroupIdentifiers::PUBLIC_MODERATOR->name, $this->byGroup);
    }

    public function findTechnicalAdministrator(): ?User
    {
        $now = $this->clock->now();

        return $this->findCached([
            'filter' => [
                '=ACTIVE' => 'Y',
                '=BLOCKED' => 'N',
                '=GROUPS.GROUP_ID' => GroupIdentifiers::TECHNICAL_ADMINISTRATOR->value,
                [
                    'LOGIC' => 'OR',
                    'GROUPS.DATE_ACTIVE_FROM' => null,
                    '<GROUPS.DATE_ACTIVE_FROM' => Date::toFormat($now, DateFormat::BITRIX),
                ],
                [
                    'LOGIC' => 'OR',
                    '=GROUPS.DATE_ACTIVE_TO' => null,
                    '>GROUPS.DATE_ACTIVE_TO' => Date::toFormat($now, DateFormat::BITRIX),
                ],
            ],
            'limit' => 1,
            'order' => ['ID' => 'ASC'],
        ], GroupIdentifiers::TECHNICAL_ADMINISTRATOR->name, $this->byGroup);
    }

    /**
     * @return User[]
     */
    public function findUsers(array $filter): array
    {
        return $this->loadUser(['filter' => $filter]);
    }

    public function getByEmail(string $email): User
    {
        return $this->findByEmail($email) ?? throw new NotFoundException('User');
    }

    public function getById(int $id): User
    {
        return $this->findById($id) ?? throw new NotFoundException('User');
    }

    public function getByLogin(string $login): User
    {
        return $this->findByLogin($login) ?? throw new NotFoundException('User');
    }

    public function getId(User $user): int
    {
        return $this->idMap[$user];
    }

    public function hasId(User $user): bool
    {
        return isset($this->idMap[$user]);
    }

    private function findCached(array $parameters, string $key, array &$cache): ?User
    {
        $cacheKey = "~{$key}";

        $cached = $cache[$cacheKey] ?? null;

        if (null !== $cached) {
            return (false === $cached) ? null : $cached;
        }

        $found = $this->loadUser($parameters);

        if ([] === $found) {
            $cache[$cacheKey] = false;

            return null;
        }

        $last = array_pop($found);

        if ([] !== $found) {
            $previous = array_pop($found);

            throw new FoundMultipleException(
                'User',
                sprintf('%d', $last->userIdentifier),
                sprintf('%d', $previous->userIdentifier),
            );
        }

        $cache[$cacheKey] = $last;

        return $last;
    }

    /**
     * @return User[]
     */
    private function loadUser(array $parameters): array
    {
        /** @var User[] $userList */
        $userList = [];

        $found = UserTable::getList([
            'select' => [
                'ACTIVE',
                'BLOCKED',
                'CONFIRM_CODE',
                'EMAIL',
                'ID',
                'LAST_NAME',
                'LID',
                'LOGIN',
                'NAME',
                'PERSONAL_BIRTHDAY',
                'PERSONAL_GENDER',
                'PHONE_AUTH.CONFIRMED',
                'PHONE_AUTH.PHONE_NUMBER',
                'UF_CITY',
                'UF_EDU_NAME',
                'UF_EDU_TYPE',
                'UF_EMAIL_CONFIRM_REQ',
                'UF_PHOSAGRO_COMPANY',
                'WORK_COMPANY',
            ],
        ] + $parameters);

        while ($row = $found->fetchRaw()) {
            $accessor = new Accessor($row);

            try {
                $cityId = $accessor->getIntParsed('UF_CITY');
                $city = $this->cityManager->findOne($cityId);
            } catch (AccessorException) {
                $city = null;
            }

            try {
                $eduTypeId = $accessor->getIntParsed('UF_EDU_TYPE');
                $eduType = Edu::from(Text::lower($this->userFieldManager->getEnumById($eduTypeId)->code));
            } catch (NullRequiredException|\ValueError) {
                $eduType = null;
            }

            try {
                $gender = $accessor->getEnum('PERSONAL_GENDER', Gender::class);
            } catch (AccessorException) {
                $gender = null;
            }

            try {
                $phosagroCompanyId = $accessor->getIntParsed('UF_PHOSAGRO_COMPANY');
                $phosagroCompany = $this->phosagroCompanyManager->findOne($phosagroCompanyId);
            } catch (AccessorException) {
                $phosagroCompany = null;
            }

            $user = new User(
                $accessor->getBoolBitrix('ACTIVE'),
                $accessor->getNullableDate('PERSONAL_BIRTHDAY'),
                $accessor->getBoolBitrix('BLOCKED'),
                $city,
                $accessor->getNullableStringTrimmed('WORK_COMPANY'),
                $accessor->getNullableStringTrimmed('CONFIRM_CODE'),
                $accessor->getNullableDate('UF_EMAIL_CONFIRM_REQ'),
                $phosagroCompany,
                $accessor->getNullableStringTrimmed('UF_EDU_NAME'),
                $eduType,
                $accessor->getNullableStringTrimmed('EMAIL'),
                $gender,
                $accessor->getNullableStringTrimmed('LOGIN'),
                $accessor->getNullableStringTrimmed('NAME'),
                '',
                $accessor->getNullableStringTrimmed('MAIN_USER_PHONE_AUTH_PHONE_NUMBER'),
                $accessor->getNullableBoolBitrix('MAIN_USER_PHONE_AUTH_CONFIRMED') ?? false,
                $accessor->getNullableStringTrimmed('LAST_NAME'),
                (int) $row['ID'],
                (string) $row['LID'],
            );

            $this->idMap[$user] = $accessor->getIntParsed('ID');
            $userList[] = $user;
        }

        return $userList;
    }
}
