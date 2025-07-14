<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use Bitrix\Main\DB\Connection;
use Bitrix\Main\Type\DateTime;
use Phosagro\System\Clock;
use Phosagro\System\Highloadblocks;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class RatingCalculator
{
    private const TOP_LIMIT = 3;

    private \DateTimeImmutable $now;

    public function __construct(
        private readonly Clock $clock,
        private readonly Connection $database,
        private readonly Highloadblocks $highloadblocks,
        private readonly RatingUpdater $ratingUpdater,
    ) {
        $this->now = $this->clock->now();
    }

    /**
     * @param RatingType|RatingType[] $typeList
     * @param int|int[]               $eventIdentifierList
     * @param int|int[]               $userIdentifierList
     *
     * @return array<int,array<int,array<int,RatingItem>>>
     */
    public function buildScoreIndex(
        array|RatingType $typeList,
        array|int $eventIdentifierList,
        array|int $userIdentifierList,
    ): array {
        /** @var array<int,array<int,array<int,RatingItem>>> $result */
        $result = [];

        if ($typeList instanceof RatingType) {
            $typeList = [$typeList];
        }

        if (\is_int($eventIdentifierList)) {
            $eventIdentifierList = [$eventIdentifierList];
        }

        if (\is_int($userIdentifierList)) {
            $userIdentifierList = [$userIdentifierList];
        }

        $getTypeValue = static fn (RatingType $type): int => $type->value;
        $getTypePeriod = fn (RatingType $type): string => Date::toFormat($type->getPeriod($this->now), DateFormat::BITRIX);

        $itemList = $this->findRatingItems([
            'filter' => [
                '=UF_TYPE' => array_map($getTypeValue, $typeList),
                '=UF_EVENT' => $eventIdentifierList,
                'UF_PERIOD' => array_map($getTypePeriod, $typeList),
                '=UF_USER' => $userIdentifierList,
            ],
        ]);

        foreach ($itemList as $item) {
            $result[$item->ratingType->value] ??= [];
            $result[$item->ratingType->value][$item->eventIdentifier] ??= [];
            $result[$item->ratingType->value][$item->eventIdentifier][$item->userIdentifier] = $item;
        }

        return $result;
    }

    public function calculate(int $eventIdentifier, int $userIdentifier): RatingResult
    {
        $this->ratingUpdater->updateRating();

        return new RatingResult(
            $this->calculateTable(RatingType::EVENT, $userIdentifier, $eventIdentifier),
            $this->calculateTable(RatingType::MONTH, $userIdentifier),
            $this->calculateTable(RatingType::TOTAL, $userIdentifier),
            $this->calculateTable(RatingType::WEEK, $userIdentifier),
        );
    }

    public function calculateScore(RatingType $type, int $eventIdentifier, int $userIdentifier): RatingItem
    {
        $index = $this->buildScoreIndex($type, $eventIdentifier, $userIdentifier);
        $item = $index[$type->value][$eventIdentifier][$userIdentifier] ?? null;

        if (null === $item) {
            return new RatingItem(
                $eventIdentifier,
                $this->now->setTimestamp(0),
                0,
                $type->getPeriod($this->now),
                0,
                $type,
                $userIdentifier,
            );
        }

        return $item;
    }

    private function calculateOf(RatingType $type, int $eventIdentifier): int
    {
        $sql = sprintf(
            <<<'SQL'
            select count(1) as cnt
            from phosagro_rating
            where UF_TYPE = %d
              and UF_EVENT = %d
              and UF_PERIOD = '%s'
            SQL,
            $type->value,
            $eventIdentifier,
            Date::toFormat($type->getPeriod($this->now), DateFormat::DB),
        );

        return (int) $this->database->queryScalar($sql);
    }

    private function calculatePlace(
        RatingType $type,
        int $eventIdentifier,
        int $score,
        \DateTimeImmutable $date,
        int $identifier,
    ): int {
        $sql = sprintf(
            <<<'SQL'
            select count(1) as cnt
            from phosagro_rating
            where UF_TYPE = %d
              and UF_EVENT = %d
              and UF_PERIOD = '%s'
              and (UF_SCORE > %d or (UF_SCORE = %d and (UF_DATE < '%s' or (UF_DATE = '%s' and ID < %d))))
            SQL,
            $type->value,
            $eventIdentifier,
            Date::toFormat($type->getPeriod($this->now), DateFormat::DB),
            $score,
            $score,
            Date::toFormat($date, DateFormat::DB),
            Date::toFormat($date, DateFormat::DB),
            $identifier,
        );

        return ((int) $this->database->queryScalar($sql)) + 1;
    }

    private function calculateTable(RatingType $type, int $user, int $event = 0): RatingTable
    {
        $score = $this->calculateScore($type, $event, $user);

        $scorePlace = 0;

        /** @var RatingRow[] $top */
        $top = [];

        $found = $this->findRatingItems([
            'filter' => [
                '=UF_TYPE' => $type->value,
                '=UF_EVENT' => $event,
                '=UF_PERIOD' => Date::toFormat($type->getPeriod($this->now), DateFormat::BITRIX),
            ],
            'limit' => self::TOP_LIMIT,
            'order' => [
                'UF_SCORE' => 'DESC',
                'UF_DATE' => 'ASC',
                'ID' => 'ASC',
            ],
        ]);

        $hasScore = false;

        $place = 0;

        foreach ($found as $item) {
            ++$place;
            $top[] = new RatingRow($item, $place);
            if ($item->ratingIdentifier === $score->ratingIdentifier) {
                $hasScore = true;
                $scorePlace = $place;
            }
        }

        if (($score->ratingIdentifier > 0) && !$hasScore) {
            $scorePlace = $this->calculatePlace(
                $type,
                $event,
                $score->ratingScore,
                $score->ratingDate,
                $score->ratingIdentifier
            );

            $top[] = new RatingRow($score, $scorePlace);
        }

        return new RatingTable(
            $this->calculateOf($type, $event),
            $scorePlace,
            $top,
        );
    }

    /**
     * @return RatingItem[]
     */
    private function findRatingItems(array $parameters): array
    {
        /** @var RatingItem[] $ratingItemList */
        $ratingItemList = [];

        $found = $this->highloadblocks->findAllHighloadblockElements('Rating', $parameters);

        foreach ($found as $row) {
            $type = RatingType::from((int) $row['UF_TYPE']);
            $ratingItemList[] = new RatingItem(
                (int) $row['UF_EVENT'],
                $this->fromBitrixDate($row['UF_DATE']),
                (int) $row['ID'],
                $type->getPeriod($this->fromBitrixDate($row['UF_PERIOD'])),
                (int) $row['UF_SCORE'],
                $type,
                (int) $row['UF_USER'],
            );
        }

        return $ratingItemList;
    }

    private function fromBitrixDate(?DateTime $value): \DateTimeImmutable
    {
        if (null === $value) {
            throw new \RuntimeException('Null date.');
        }

        return new \DateTimeImmutable(sprintf('@%d', $value->getTimestamp()));
    }
}
