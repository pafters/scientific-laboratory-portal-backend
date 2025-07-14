<?php

declare(strict_types=1);

namespace Phosagro\Voting;

use Bitrix\Main\EventManager;
use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\VotingManager;
use Phosagro\Subscription\RubricCode;
use Phosagro\Subscription\RubricMap;
use Phosagro\System\ListenerInterface;

final class SubscriptionConditionsChecker implements ListenerInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly RubricMap $rubrics,
        private readonly UserManager $users,
        private readonly Voter $voter,
        private readonly VotingManager $votings,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('subscribe', 'BeforePostingSendMail', $this->executeListener(...));
    }

    private function executeListener(array $fields): array|false
    {
        $postingIdentifier = ($fields['POSTING_ID'] ?? null);
        $postingIdentifier = filter_var($postingIdentifier, FILTER_VALIDATE_INT);
        $postingIdentifier = (\is_int($postingIdentifier) ? $postingIdentifier : null);

        if (null === $postingIdentifier) {
            return $fields;
        }

        $isVotingRubric = false;

        $foundRubric = \CPosting::GetRubricList($postingIdentifier);

        while ($rowRubric = $foundRubric->Fetch()) {
            $rubric = $this->rubrics->findRubricByIdentifier((int) $rowRubric['ID']);
            if (RubricCode::VOTINGS === $rubric->getKnownCode()) {
                $isVotingRubric = true;

                break;
            }
        }

        if (!$isVotingRubric) {
            return $fields;
        }

        $email = $fields['EMAIL'] ?? null;
        $email = (\is_string($email) ? trim($email) : '');

        if ('' === $email) {
            $this->logger->log(
                LogEvent::SUBSCRIPTION_SENDING_FAILED,
                sprintf('%d', $postingIdentifier),
                sprintf('Empty email for posting "%d".', $postingIdentifier),
            );

            return false;
        }

        $foundSubscription = \CSubscription::GetByEmail($email);

        $firstSubscription = $foundSubscription->Fetch();

        if (!$firstSubscription) {
            $this->logger->log(
                LogEvent::SUBSCRIPTION_SENDING_FAILED,
                sprintf('%d', $postingIdentifier),
                sprintf('No subscription for posting "%d" email "%s".', $postingIdentifier, $email),
            );

            return false;
        }

        if ($foundSubscription->Fetch()) {
            $this->logger->log(
                LogEvent::SUBSCRIPTION_SENDING_FAILED,
                sprintf('%d', $postingIdentifier),
                sprintf('Multiple subscription for posting "%d" email "%s".', $postingIdentifier, $email),
            );

            return false;
        }

        $userIdentifier = ($firstSubscription['USER_ID'] ?? null);
        $userIdentifier = filter_var($userIdentifier, FILTER_VALIDATE_INT);
        $userIdentifier = (\is_int($userIdentifier) ? $userIdentifier : null);

        if (null === $userIdentifier) {
            $this->logger->log(
                LogEvent::SUBSCRIPTION_SENDING_FAILED,
                sprintf('%d', $postingIdentifier),
                sprintf('Anonymous subscription for posting "%d" email "%s".', $postingIdentifier, $email),
            );

            return false;
        }

        $user = $this->users->findById($userIdentifier);

        if (null === $user) {
            $this->logger->log(
                LogEvent::SUBSCRIPTION_SENDING_FAILED,
                sprintf('%d', $postingIdentifier),
                sprintf('Deleted subscription user for posting "%d" email "%s".', $postingIdentifier, $email),
            );

            return false;
        }

        try {
            $this->votings->getUserVotingForPosting($user, $postingIdentifier);
        } catch (NotFoundException) {
            $this->logger->log(
                LogEvent::SUBSCRIPTION_SENDING_FAILED,
                sprintf('%d', $postingIdentifier),
                sprintf('Voting denied for subscription user for posting "%d" email "%s".', $postingIdentifier, $email),
            );

            return false;
        }

        return $fields;
    }
}
