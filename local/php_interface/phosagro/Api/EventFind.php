<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\EventToApiConverter;
use Phosagro\Manager\AgeCategoryManager;
use Phosagro\Manager\CityManager;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\GroupManager;
use Phosagro\Manager\PartnerManager;
use Phosagro\Object\Group;
use Phosagro\System\Api\Errors\BadRequestError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class EventFind
{
    public function __construct(
        private readonly AgeCategoryManager $ageCategoryManager,
        private readonly AuthorizationContext $authorizationContext,
        private readonly CityManager $cityManager,
        private readonly EventManager $eventManager,
        private readonly EventToApiConverter $eventToApiConverter,
        private readonly GroupManager $groupManager,
        private readonly PartnerManager $partnerManager,
    ) {}

    #[Route(pattern: '~^/api/event/find/(?:\?.*)?$~')]
    public function execute(): array
    {
        $filter = [
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];

        // === AGE ===

        $ageCategoryCode = filter_input(INPUT_GET, 'age');
        $ageCategoryCode = (\is_string($ageCategoryCode) ? trim($ageCategoryCode) : '');

        if ('' !== $ageCategoryCode) {
            do {
                $ageCategoryIdentifier = filter_var($ageCategoryCode, FILTER_VALIDATE_INT);

                if (!\is_int($ageCategoryIdentifier)) {
                    $filter['ID'] = false;

                    break;
                }

                $ageCategory = $this->ageCategoryManager->findOne($ageCategoryIdentifier);

                if (null === $ageCategory) {
                    $filter['ID'] = false;

                    break;
                }

                $filter['PROPERTY_AGE_CATEGORY'] = $ageCategory->ageCategoryIdentifier;
            } while (false);
        }

        // === ARCHIVED ===

        $archivedValue = filter_input(INPUT_GET, 'archived');
        $archivedValue = (\is_string($archivedValue) ? trim($archivedValue) : '');

        if ('' !== $archivedValue) {
            if (filter_var($archivedValue, FILTER_VALIDATE_BOOL)) {
                $filter['!PROPERTY_ARCHIVED'] = false;
            } else {
                $filter['PROPERTY_ARCHIVED'] = false;
            }
        }

        // === CITY ===

        $cityCode = filter_input(INPUT_GET, 'city');
        $cityCode = (\is_string($cityCode) ? trim($cityCode) : '');

        if ('' !== $cityCode) {
            do {
                $city = $this->cityManager->findByCode($cityCode);

                if (null === $city) {
                    $filter['ID'] = false;

                    break;
                }

                $cityIdentifier = $this->cityManager->findBitrixId($city);

                if (null === $cityIdentifier) {
                    $filter['ID'] = false;

                    break;
                }

                $filter['PROPERTY_CITY'] = $cityIdentifier;
            } while (false);
        }

        // === FIND AFTER ===

        $findAfterValue = filter_input(INPUT_GET, 'findAfter');
        $findAfterValue = (\is_string($findAfterValue) ? trim($findAfterValue) : '');

        if ('' !== $findAfterValue) {
            do {
                $findAfter = Date::tryFromFormat($findAfterValue, DateFormat::BITRIX_DATE);

                if (null === $findAfter) {
                    $filter['ID'] = false;

                    break;
                }

                $filter[] = [
                    '>=PROPERTY_ENDS_AT' => Date::toFormat($findAfter, DateFormat::DB),
                    'LOGIC' => 'OR',
                    'PROPERTY_ENDS_AT' => false,
                ];
            } while (false);
        }

        // === FIND BEFORE ===

        $findBeforeValue = filter_input(INPUT_GET, 'findBefore');
        $findBeforeValue = (\is_string($findBeforeValue) ? trim($findBeforeValue) : '');

        if ('' !== $findBeforeValue) {
            do {
                $findBefore = Date::tryFromFormat($findBeforeValue, DateFormat::BITRIX_DATE);

                if (null === $findBefore) {
                    $filter['ID'] = false;

                    break;
                }

                $filter['<PROPERTY_STARTS_AT'] = Date::toFormat($findBefore, DateFormat::DB);
            } while (false);
        }

        // === LIMIT ===

        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);

        if (null === $limit) {
            $limit = 10;
        }

        if (!\is_int($limit) || ($limit < 1)) {
            throw new \RuntimeException('Wrong limit.');
        }

        // === PAGE ===

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

        if (null === $page) {
            $page = 1;
        }

        if (!\is_int($page) || ($page < 1)) {
            throw new BadRequestError('wrong_page');
        }

        // === PARTNER ===

        $partnerCode = filter_input(INPUT_GET, 'partner');
        $partnerCode = (\is_string($partnerCode) ? trim($partnerCode) : '');

        if ('' !== $partnerCode) {
            do {
                $partnerIdentifier = filter_var($partnerCode, FILTER_VALIDATE_INT);

                if (!\is_int($partnerIdentifier)) {
                    $filter['ID'] = false;

                    break;
                }

                $partner = $this->partnerManager->findOne($partnerIdentifier);

                if (null === $partner) {
                    $filter['ID'] = false;

                    break;
                }

                $filter['PROPERTY_PARTNERS'] = $partnerIdentifier;
            } while (false);
        }

        // === USER GROUPS ===

        $user = $this->authorizationContext->getNullableAuthorizedUser();

        if (null !== $user) {
            $filter[] = [
                'LOGIC' => 'OR',
                [
                    'PROPERTY_DISPLAY_GROUPS' => false,
                ],
                [
                    'PROPERTY_DISPLAY_GROUPS' => array_map(
                        static fn (Group $g): int => $g->groupIdentifier,
                        $this->groupManager->findGroupsForUser($user->userIdentifier)
                    ),
                ],
            ];
        } else {
            $filter['PROPERTY_DISPLAY_GROUPS'] = false;
        }

        $foundEvents = $this->eventManager->findAllElements($filter, nav: [
            'bShowAll' => false,
            'checkOutOfRange' => true,
            'iNumPage' => $page,
            'nPageSize' => $limit,
        ]);

        $eventResult = [];

        $eventDataIndex = $this->eventToApiConverter->convertEventsToApi($foundEvents);

        foreach ($foundEvents as $event) {
            $eventResult[] = $eventDataIndex[$event];
        }

        return [
            'events' => $eventResult,
        ];
    }
}
