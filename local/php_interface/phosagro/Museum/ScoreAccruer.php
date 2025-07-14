<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\MuseumObjectManager;
use Phosagro\Manager\MuseumVisitManager;
use Phosagro\Manager\ScoreManager;
use Phosagro\Object\AccrualReasonCode;
use Phosagro\Object\DatabaseChanges;
use Phosagro\Object\MuseumVisit;
use Phosagro\Util\Text;

final class ScoreAccruer
{
    /** @var null|\SplObjectStorage<MuseumVisit,int> */
    private ?\SplObjectStorage $changesCache = null;

    /** @var null|MuseumVisit[] */
    private ?array $notAccruedCache = null;

    /** @var array<int,array<string,int>> */
    private ?array $statusScoreCache = null;

    public function __construct(
        private readonly MuseumObjectManager $museumObjects,
        private readonly MuseumVisitManager $museumVisits,
        private readonly ScoreManager $scores,
        private readonly UserManager $users,
    ) {}

    public function accrueScore(): void
    {
        $changes = $this->calculateChanges();

        foreach ($changes as $visit) {
            $this->accrueScoreForVisit($visit, $changes[$visit]);
        }
    }

    private function accrueScoreForVisit(MuseumVisit $visit, int $score): void
    {
        if ($score <= 0) {
            return;
        }

        $user = $this->users->findById($visit->museumVisitUserIdentifier);

        if (null === $user) {
            return;
        }

        $this->scores->addScore($user, AccrualReasonCode::MUSEUM_VISIT, $visit, $score);

        $this->museumVisits->saveChanges(new DatabaseChanges(changed: [
            $visit->museumVisitIdentifier => [
                'UF_ACCRUED' => '1',
            ],
        ]));
    }

    /**
     * @return \SplObjectStorage<MuseumVisit,int>
     */
    private function calculateChanges(): \SplObjectStorage
    {
        if (null !== $this->changesCache) {
            return $this->changesCache;
        }

        $this->changesCache = new \SplObjectStorage();

        $scoreIndex = $this->getStatusScore();

        foreach ($this->getWaitingForAccrual() as $visit) {
            $objectKey = $visit->museumVisitObjectIdentifier;
            $statusKey = '~'.self::makeKey($visit->museumVisitStatus);
            $score = $scoreIndex[$objectKey][$statusKey] ?? 0;
            $this->changesCache->attach($visit, $score);
        }

        return $this->changesCache;
    }

    /**
     * @return array<int,array<string,int>>
     */
    private function getStatusScore(): array
    {
        if (null !== $this->statusScoreCache) {
            return $this->statusScoreCache;
        }

        $this->statusScoreCache = [];

        foreach ($this->museumObjects->getActiveMuseumObjects() as $object) {
            $objectKey = $object->museumObjectIdentifier;
            $this->statusScoreCache[$objectKey] ??= [];
            foreach ($object->museumObjectStatusList as $status) {
                $statusKey = '~'.self::makeKey($status->museumObjectStatusCode);
                $this->statusScoreCache[$objectKey][$statusKey] = $status->museumObjectStatusBonus;
            }
        }

        return $this->statusScoreCache;
    }

    /**
     * @return MuseumVisit[]
     */
    private function getWaitingForAccrual(): array
    {
        return $this->notAccruedCache ??= $this->museumVisits->getNotAcruedVisits();
    }

    private static function makeKey(string $text): string
    {
        return Text::replace(Text::upper(trim($text)), '~\s+~', ' ');
    }
}
