<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlHelper;
use Phosagro\System\Clock;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class RatingCleaner
{
    private const OPTION_MODULE = 'phosagro';
    private const RATING_CLEANUP = 'RATING_CLEANUP';

    private readonly SqlHelper $helper;

    public function __construct(
        private readonly Clock $clock,
        private readonly Connection $database,
    ) {
        $this->helper = $database->getSqlHelper();
    }

    public function cleanupRating(): void
    {
        $this->database->startTransaction();

        try {
            $now = $this->clock->now();
            $ratingDate = $this->lockRatingDate();
            $this->performCleaning(RatingType::WEEK, $now, $ratingDate);
            $this->performCleaning(RatingType::MONTH, $now, $ratingDate);
            $this->saveRatingDate($now);
        } catch (\Throwable $error) {
            $this->database->rollbackTransaction();

            throw $error;
        }

        $this->database->commitTransaction();
    }

    private function lockRatingDate(): \DateTimeImmutable
    {
        $value = (string) $this->database->queryScalar(sprintf(
            <<<'SQL'
            select VALUE
            from b_option
            where MODULE_ID = '%s'
              and NAME = '%s'
            SQL,
            $this->helper->forSql(self::OPTION_MODULE),
            $this->helper->forSql(self::RATING_CLEANUP),
        ));

        $date = Date::tryFromFormat($value, DateFormat::DB);

        if (null === $date) {
            return new \DateTimeImmutable('@0');
        }

        return $date;
    }

    private function performCleaning(RatingType $type, \DateTimeImmutable $now, \DateTimeImmutable $ratingDate): void
    {
        $nowPeriod = $type->getPeriod($now);
        $ratingPeriod = $type->getPeriod($ratingDate);

        if ($ratingPeriod->getTimestamp() === $nowPeriod->getTimestamp()) {
            return;
        }

        $sql = sprintf(
            <<<'SQL'
                delete from phosagro_rating
                where UF_TYPE = %d and UF_EVENT = 0 and UF_PERIOD < '%s'
                SQL,
            $type->value,
            Date::toFormat($nowPeriod, DateFormat::DB),
        );

        $this->database->queryExecute($sql);
    }

    private function saveRatingDate(\DateTimeImmutable $ratingDate): void
    {
        $value = Date::toFormat($ratingDate, DateFormat::DB);
        \COption::SetOptionString(self::OPTION_MODULE, self::RATING_CLEANUP, $value);
    }
}
