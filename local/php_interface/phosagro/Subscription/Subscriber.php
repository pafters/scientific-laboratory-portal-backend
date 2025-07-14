<?php

declare(strict_types=1);

namespace Phosagro\Subscription;

use Phosagro\Object\Bitrix\User;
use Phosagro\System\Clock;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

/**
 * Управление подписками пользователя.
 *
 * Работает с объектами Subscriptions.
 */
final class Subscriber
{
    public function __construct(
        private readonly Clock $clock,
        private readonly RubricMap $rubrics,
    ) {}

    public function getSubscriptions(User $user): Subscriptions
    {
        $subscriptions = new Subscriptions();

        $foundSubscription = \CSubscription::GetByEmail($user->email, $user->userIdentifier);

        $firstSubscription = $foundSubscription->Fetch();

        if ($firstSubscription) {
            $foundRubric = \CSubscription::GetRubricList((int) $firstSubscription['ID']);
            while ($rowRubric = $foundRubric->Fetch()) {
                $rubric = $this->rubrics->findRubricByIdentifier((int) $rowRubric['ID']);
                if (null !== $rubric) {
                    $code = $rubric->getKnownCode();
                    if (null !== $code) {
                        $subscriptions->markSubscribed($code);
                    }
                }
            }
        }

        return $subscriptions;
    }

    public function setSubscriptions(User $user, Subscriptions $subscriptions): void
    {
        $manager = new \CSubscription();

        $found = \CSubscription::GetByEmail($user->email, $user->userIdentifier);

        $first = $found->Fetch();

        /** @var int[] $deletingList */
        $deletingList = [];

        while ($trash = $found->Fetch()) {
            $deletingList[] = (int) $trash['ID'];
        }

        foreach ($deletingList as $deleting) {
            $deleteResult = \CSubscription::Delete($deleting);

            if (!$deleteResult) {
                throw new \RuntimeException(sprintf('Can not delete subscription "%d".', $deleting));
            }
        }

        $now = $this->clock->now();

        /** @var int[] $rubricList */
        $rubricList = [];

        foreach (RubricCode::cases() as $code) {
            if ($subscriptions->isSubscribed($code)) {
                $rubricList[] = $this->rubrics->getKnownRubric($code)->rubricIdentifier;
            }
        }

        if ($first) {
            $identifier = (int) $first['ID'];

            $updateResult = $manager->Update($identifier, [
                'ACTIVE' => 'Y',
                'ALL_SITES' => 'Y',
                'CONFIRMED' => 'Y',
                'CONFIRM_CODE' => 'XXXXXXXX',
                'DATE_UPDATE' => Date::toFormat($now, DateFormat::BITRIX),
                'FORMAT' => 'text',
                'RUB_ID' => $rubricList,
                'SEND_CONFIRM' => 'N',
            ]);

            if (!$updateResult) {
                throw new \RuntimeException(sprintf(
                    'Can not update subscription "%d". %s',
                    $identifier,
                    $manager->LAST_ERROR,
                ));
            }
        } else {
            $addResult = $manager->Add([
                'ACTIVE' => 'Y',
                'ALL_SITES' => 'Y',
                'CONFIRMED' => 'Y',
                'CONFIRM_CODE' => 'XXXXXXXX',
                'DATE_CONFIRM' => Date::toFormat($now, DateFormat::BITRIX),
                'DATE_INSERT' => Date::toFormat($now, DateFormat::BITRIX),
                'DATE_UPDATE' => Date::toFormat($now, DateFormat::BITRIX),
                'EMAIL' => $user->email,
                'FORMAT' => 'text',
                'RUB_ID' => $rubricList,
                'SEND_CONFIRM' => 'N',
                'USER_ID' => $user->userIdentifier,
            ]);

            if (!$addResult) {
                throw new \RuntimeException(sprintf(
                    'Can not add subscription for user "%d" with email "%s". %s',
                    $user->userIdentifier,
                    $user->email,
                    $manager->LAST_ERROR,
                ));
            }
        }
    }
}
