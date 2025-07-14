<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\Manager\Errors\FoundMultipleException;
use Phosagro\Object\AccrualReasonCode;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Event;
use Phosagro\Object\MuseumVisit;
use Phosagro\Object\Score;
use Phosagro\Object\ScoreForUser;
use Phosagro\Object\Task;
use Phosagro\Rating\RatingCalculator;
use Phosagro\Rating\RatingType;
use Phosagro\System\Clock;
use Phosagro\System\Highloadblocks;

final class ScoreManager
{
    public function __construct(
        private readonly AccrualReasonManager $accrualReasons,
        private readonly Clock $clock,
        private readonly Highloadblocks $highloadblocks,
        private readonly Logger $logger,
        private readonly RatingCalculator $ratingCalculator,
    ) {}

    public function addScore(User $user, AccrualReasonCode $reason, mixed $subject, int $amount): void
    {
        if (0 === $amount) {
            return;
        }

        $accrualReason = $this->accrualReasons->findSingleElement([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'CODE' => $reason->value,
        ]);

        if (null === $accrualReason) {
            $this->logger->log(LogEvent::ACCRUAL_REASON_MISSING, $reason->value, sprintf(
                '[%d] %s / [%d] %s',
                $user->userIdentifier,
                $user->login,
                $this->getSubjectIdentifier($reason, $subject),
                $this->getSubjectComment($reason, $subject),
            ));

            return;
        }

        $this->highloadblocks->addHighloadblockElement('Score', [
            'UF_AMOUNT' => $amount,
            'UF_COMMENT' => $this->getSubjectComment($reason, $subject),
            'UF_DATE' => ConvertTimeStamp($this->clock->now()->getTimestamp(), 'FULL'),
            'UF_REASON' => $accrualReason->reasonIdentifier,
            'UF_SUBJECT' => $this->getSubjectIdentifier($reason, $subject),
            'UF_USER' => $user->userIdentifier,
        ]);
    }

    public function calculateUserScore(int $userIdentifier): ScoreForUser
    {
        $monthScore = $this->ratingCalculator->calculateScore(RatingType::MONTH, 0, $userIdentifier);
        $totalScore = $this->ratingCalculator->calculateScore(RatingType::TOTAL, 0, $userIdentifier);
        $weekScore = $this->ratingCalculator->calculateScore(RatingType::WEEK, 0, $userIdentifier);

        return new ScoreForUser(
            $monthScore->ratingScore,
            $totalScore->ratingScore,
            $weekScore->ratingScore,
        );
    }

    public function findAllScores(array $filter): array
    {
        $result = [];

        $found = $this->highloadblocks->findAllHighloadblockElements('Score', ['filter' => $filter]);

        foreach ($found as $row) {
            $result[] = new Score(
                (int) $row['UF_REASON'],
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['UF_DATE']),
                (int) $row['UF_AMOUNT'],
                (string) $row['UF_COMMENT'],
                (int) $row['ID'],
                (string) $row['UF_SUBJECT'],
                (int) $row['UF_USER'],
            );
        }

        return $result;
    }

    public function findFirstScore(array $filter): ?Score
    {
        $found = $this->findAllScores($filter);

        if ([] === $found) {
            return null;
        }

        return array_shift($found);
    }

    public function findSingleScore(array $filter): ?Score
    {
        $found = $this->findAllScores($filter);

        if ([] === $found) {
            return null;
        }

        $first = array_shift($found);

        if ([] !== $found) {
            $second = array_shift($found);

            throw new FoundMultipleException(
                Score::class,
                sprintf('%d', $first->completionIdentifier),
                sprintf('%d', $second->completionIdentifier),
            );
        }

        return $first;
    }

    public function hasScore(User $user, AccrualReasonCode $reason, mixed $subject): bool
    {
        $accrualReason = $this->accrualReasons->findSingleElement([
            'CODE' => $reason->value,
        ]);

        if (null === $accrualReason) {
            return false;
        }

        $foundScore = $this->highloadblocks->findFirstHighloadblockElement('Score', [
            'filter' => [
                '=UF_REASON' => $accrualReason->reasonIdentifier,
                '=UF_SUBJECT' => $this->getSubjectIdentifier($reason, $subject),
                '=UF_USER' => $user->userIdentifier,
            ],
        ]);

        return null !== $foundScore;
    }

    private function getSubjectComment(AccrualReasonCode $code, mixed $subject): string
    {
        if (AccrualReasonCode::ADMIN_DECISION === $code) {
            if ($subject instanceof User) {
                return $subject->login;
            }

            $this->throwSubjectTypeError($code, User::class, $subject);
        }

        if (AccrualReasonCode::EVENT_COMPLETION === $code) {
            if ($subject instanceof Event) {
                return $subject->name;
            }

            $this->throwSubjectTypeError($code, Event::class, $subject);
        }

        if (AccrualReasonCode::MUSEUM_VISIT === $code) {
            if ($subject instanceof MuseumVisit) {
                return sprintf('%d', $subject->museumVisitIdentifier);
            }

            $this->throwSubjectTypeError($code, MuseumVisit::class, $subject);
        }

        if (AccrualReasonCode::TASK_COMPLETION === $code) {
            if ($subject instanceof Task) {
                return $subject->taskName;
            }

            $this->throwSubjectTypeError($code, Task::class, $subject);
        }

        $this->throwSubjectTypeError($code, 'null', $subject);
    }

    private function getSubjectIdentifier(AccrualReasonCode $code, mixed $subject): string
    {
        if (AccrualReasonCode::ADMIN_DECISION === $code) {
            if ($subject instanceof User) {
                return sprintf('%d', $subject->userIdentifier);
            }

            $this->throwSubjectTypeError($code, User::class, $subject);
        }

        if (AccrualReasonCode::EVENT_COMPLETION === $code) {
            if ($subject instanceof Event) {
                return sprintf('%d', $subject->id);
            }

            $this->throwSubjectTypeError($code, Event::class, $subject);
        }

        if (AccrualReasonCode::MUSEUM_VISIT === $code) {
            if ($subject instanceof MuseumVisit) {
                return sprintf('%d', $subject->museumVisitIdentifier);
            }

            $this->throwSubjectTypeError($code, MuseumVisit::class, $subject);
        }

        if (AccrualReasonCode::TASK_COMPLETION === $code) {
            if ($subject instanceof Task) {
                return sprintf('%d', $subject->taskIdentifier);
            }

            $this->throwSubjectTypeError($code, Task::class, $subject);
        }

        $this->throwSubjectTypeError($code, 'undefined', $subject);
    }

    private function throwSubjectTypeError(AccrualReasonCode $code, string $expected, mixed $actual): never
    {
        $actualType = get_debug_type($actual);

        throw new \LogicException(sprintf('Subject of %s must be %s, got %s.', $code->name, $expected, $actualType));
    }
}
