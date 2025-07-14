<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use Bitrix\Main\DB\Connection;
use Bitrix\Main\DB\SqlHelper;
use Phosagro\Iblocks;
use Phosagro\Manager\AccrualReasonManager;
use Phosagro\Object\AccrualReasonCode;
use Phosagro\System\Iblock\Properties;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class RatingUpdater
{
    private const LAST_SCORE = 'LAST_SCORE';
    private const OPTION_MODULE = 'phosagro';

    private readonly SqlHelper $helper;

    public function __construct(
        private readonly AccrualReasonManager $accrualReasons,
        private readonly Connection $database,
        private readonly Properties $properties,
    ) {
        $this->helper = $database->getSqlHelper();
    }

    public function updateRating(): void
    {
        $this->database->startTransaction();

        try {
            $lastScore = $this->lockLastScore();
            $nextScore = $this->obtainNextScore();
            $this->performCalculation($lastScore, $nextScore);
            $this->saveLastScore($nextScore);
        } catch (\Throwable $error) {
            $this->database->rollbackTransaction();

            throw $error;
        }

        $this->database->commitTransaction();
    }

    private function lockLastScore(): int
    {
        return (int) $this->database->queryScalar(sprintf(
            <<<'SQL'
            select VALUE
            from b_option
            where MODULE_ID = '%s'
              and NAME = '%s'
            SQL,
            $this->helper->forSql(self::OPTION_MODULE),
            $this->helper->forSql(self::LAST_SCORE),
        ));
    }

    private function obtainNextScore(): int
    {
        return (int) $this->database->queryScalar('select max(ID) from phosagro_score');
    }

    private function performCalculation(int $lastScore, int $nextScore): void
    {
        $reasons = $this->accrualReasons->getReasonIndex([
            AccrualReasonCode::EVENT_COMPLETION,
            AccrualReasonCode::TASK_COMPLETION,
        ]);

        $sql = sprintf(
            <<<'SQL'
            insert into phosagro_rating (UF_DATE, UF_EVENT, UF_PERIOD, UF_SCORE, UF_TYPE, UF_USER)
            select
                # === Дата ===
                max(score.UF_DATE) as rating_date,
                # === Событие ===
                case t.score_type
                when %d then
                    case score.UF_REASON
                    when '%d' then cast(score.UF_SUBJECT as signed integer)
                    when '%d' then task_properties.PROPERTY_%u
                    else 0
                    end
                else 0
                end as rating_event,
                # === Период ===
                case t.score_type
                when %d then concat(
                    date(date_sub(score.UF_DATE, interval weekday(score.UF_DATE) day)),
                    ' 00:00:00'
                )
                when %d then concat(
                    date(date_sub(score.UF_DATE, interval greatest(0, dayofmonth(score.UF_DATE) - 1) day)),
                    ' 00:00:00'
                )
                else '%s'
                end as rating_period,
                # === Баллы ===
                sum(score.UF_AMOUNT) as rating_score,
                # === Тип ===
                t.score_type as rating_type,
                # === Пользователь ===
                score.UF_USER as rating_user
            from phosagro_score as score
            left join b_iblock_element_prop_s%u as task_properties
            on score.UF_REASON = '%d'
            and score.UF_SUBJECT = task_properties.IBLOCK_ELEMENT_ID
            inner join (
                select %d as score_type
                union
                select %d as score_type
                union
                select %d as score_type
                union
                select %d as score_type
            ) as t
            where score.ID between %d and %d
            group by rating_type, rating_event, rating_period, rating_user
            on duplicate key update
                UF_DATE = greatest(UF_DATE, values(UF_DATE)),
                UF_SCORE = UF_SCORE + values(UF_SCORE)
            SQL,
            RatingType::EVENT->value,
            $reasons[AccrualReasonCode::EVENT_COMPLETION]->reasonIdentifier,
            $reasons[AccrualReasonCode::TASK_COMPLETION]->reasonIdentifier,
            $this->properties->getPropertyId(Iblocks::taskId(), 'EVENT'),
            RatingType::WEEK->value,
            RatingType::MONTH->value,
            $this->helper->forSql(Date::toFormat(new \DateTimeImmutable('@0'), DateFormat::DB)),
            Iblocks::taskId(),
            $reasons[AccrualReasonCode::TASK_COMPLETION]->reasonIdentifier,
            RatingType::WEEK->value,
            RatingType::MONTH->value,
            RatingType::TOTAL->value,
            RatingType::EVENT->value,
            $lastScore + 1,
            $nextScore,
        );

        $this->database->queryExecute($sql);
    }

    private function saveLastScore(int $lastScore): void
    {
        \COption::SetOptionInt(self::OPTION_MODULE, self::LAST_SCORE, sprintf('%d', $lastScore));
    }
}
