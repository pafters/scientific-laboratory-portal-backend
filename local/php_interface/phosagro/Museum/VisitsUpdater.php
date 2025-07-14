<?php

declare(strict_types=1);

namespace Phosagro\Museum;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\MuseumObjectManager;
use Phosagro\Manager\MuseumVisitManager;
use Phosagro\Object\DatabaseChanges;
use Phosagro\Object\MuseumObject;
use Phosagro\Object\MuseumVisit;
use Phosagro\System\Clock;
use Phosagro\Util\Collection;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;
use Phosagro\Util\Phone;
use Phosagro\Util\Text;

final class VisitsUpdater
{
    private ?DatabaseChanges $changesCache = null;

    /** @var array<string,array<string,array<string,array<int,string>>>> */
    private ?array $museumDatabaseData = null;

    /** @var null|MuseumObject[] */
    private ?array $museumObjectCache = null;

    /** @var array<int,array<string,array<string,MuseumVisit>>> */
    private ?array $savedStatus = null;

    /** @var array<string,array<string,int>> */
    private ?array $userIndex = null;

    public function __construct(
        private readonly Clock $clock,
        private readonly FileFinder $files,
        private readonly MuseumObjectManager $museumObjects,
        private readonly MuseumVisitManager $museumVisits,
        private readonly UserManager $users,
    ) {}

    public function updateVisits(): void
    {
        $this->museumVisits->saveChanges($this->calculateChanges());
    }

    private function calculateChanges(): DatabaseChanges
    {
        if (null !== $this->changesCache) {
            return $this->changesCache;
        }

        /** @var array[] $added */
        $added = [];

        /** @var array<int,array> $changed */
        $changed = [];

        /** @var array<int,null> $deleted */
        $deleted = [];

        $file = $this->files->findLastDatabaseFile();

        if ($file) {
            $now = Date::toFormat($this->clock->now(), DateFormat::BITRIX);
            $savedState = $this->getSavedState();
            $userIndex = $this->getUserIndex();
            foreach ($this->getMuseumDatabaseData($file) as $phoneKey => $phoneData) {
                foreach ($phoneData as $birthdayKey => $birthdayData) {
                    $userIdentifier = $userIndex[$phoneKey][$birthdayKey] ?? null;
                    if (null !== $userIdentifier) {
                        $savedVisitIndex = $savedState[$userIdentifier] ?? [];
                        foreach ($birthdayData as $visitKey => $visitData) {
                            $savedVisitData = $savedVisitIndex[$visitKey] ?? null;
                            $visitIdentifier = Text::removePrefix($visitKey, '~');
                            if (null === $savedVisitData) {
                                foreach ($visitData as $addedObject => $addedStatus) {
                                    $added[] = [
                                        'UF_ACCRUED' => '0',
                                        'UF_DATE' => $now,
                                        'UF_OBJECT' => $addedObject,
                                        'UF_STATUS' => $addedStatus,
                                        'UF_USER' => $userIdentifier,
                                        'UF_VISIT' => $visitIdentifier,
                                    ];
                                }
                            } else {
                                foreach ($visitData as $changedObject => $changedStatus) {
                                    $savedVisit = $savedVisitData[$changedObject] ?? null;
                                    if (null === $savedVisit) {
                                        $added[] = [
                                            'UF_ACCRUED' => '0',
                                            'UF_DATE' => $now,
                                            'UF_OBJECT' => $changedObject,
                                            'UF_STATUS' => $changedStatus,
                                            'UF_USER' => $userIdentifier,
                                            'UF_VISIT' => $visitIdentifier,
                                        ];
                                    } else {
                                        if ($savedVisit->museumVisitStatus !== $changedStatus) {
                                            $changed[$savedVisit->museumVisitIdentifier] = [
                                                'UF_DATE' => $now,
                                                'UF_STATUS' => $changedStatus,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->changesCache = new DatabaseChanges(
            $added,
            $changed,
            Collection::identifierList(array_keys($deleted)),
        );

        return $this->changesCache;
    }

    private static function fetchField(array $row, string $field): string
    {
        if (!\array_key_exists($field, $row)) {
            throw new \RuntimeException(sprintf('Not found field "%s".', $field));
        }

        $value = $row[$field] ?? '';
        $value = \is_float($value) ? sprintf('%F', $value) : $value;
        $value = \is_int($value) ? sprintf('%d', $value) : $value;
        $value = \is_string($value) ? trim($value) : $value;

        if (!\is_string($value)) {
            throw new \RuntimeException(sprintf('Not a string field "%s".', $field));
        }

        return $value;
    }

    /**
     * Возвращает структуру [~телефон⇾[~дата⇾[~посещение⇾[~объект⇾статус]]]].
     *
     * @return array<string,array<string,array<string,array<int,string>>>>
     *
     * @throws MuseumException
     */
    private function getMuseumDatabaseData(\SplFileInfo $databaseFile): array
    {
        if (null !== $this->museumDatabaseData) {
            return $this->museumDatabaseData;
        }

        $museumObjectList = $this->getMuseumObjects();

        $extension = $databaseFile->getExtension();

        if ('db' !== $extension) {
            throw new MuseumException(GetMessage('MUSEUM_DATABASE_WRONG_EXTENSION', [
                '#EXTENSION#' => $extension,
            ]), $databaseFile->getBasename());
        }

        try {
            $sqlite = new \SQLite3($databaseFile->getPathname(), SQLITE3_OPEN_READONLY);
        } catch (\Throwable $fileFormatError) {
            throw new MuseumException(GetMessage('MUSEUM_DATABASE_WRONG_FORMAT', [
                '#ERROR_CODE#' => $fileFormatError->getCode(),
                '#ERROR_MESSAGE#' => $fileFormatError->getMessage(),
            ]), $databaseFile->getBasename(), $fileFormatError);
        }

        $found = $sqlite->query('select * from Clients;');

        if (false === $found) {
            throw new MuseumException(GetMessage('MUSEUM_DATABASE_WRONG_STRUCTURE', [
                '#ERROR_CODE#' => $sqlite->lastErrorCode(),
                '#ERROR_MESSAGE#' => $sqlite->lastErrorMsg(),
            ]), $databaseFile->getBasename());
        }

        $rowNumber = 0;

        while ($row = $found->fetchArray(SQLITE3_ASSOC)) {
            ++$rowNumber;

            try {
                $birthday = self::fetchField($row, 'Birthday');
                $birthday = Text::replace($birthday, '~[^\d\.]+~');
                $birthday = Date::fromFormat($birthday, DateFormat::MUSEUM_DATE);
                $identifier = self::fetchField($row, 'ID');
                $phone = self::fetchField($row, 'Phone_Number');
                $phone = Phone::tryNormalizePhone($phone) ?? $phone;
            } catch (\Throwable $dataError) {
                throw new MuseumException(GetMessage('MUSEUM_DATABASE_WRONG_DATA', [
                    '#ERROR_CODE#' => $dataError->getCode(),
                    '#ERROR_MESSAGE#' => $dataError->getMessage(),
                    '#ROW#' => sprintf('%d', $rowNumber),
                ]), $databaseFile->getBasename(), $dataError);
            }

            /** @var array<int,string> $statusIndex */
            $statusIndex = [];

            $museumObjectCode = '';

            try {
                foreach ($museumObjectList as $museumObject) {
                    $museumObjectCode = $museumObject->museumObjectCode;
                    $statusIndex[$museumObject->museumObjectIdentifier] = self::fetchField($row, $museumObjectCode);
                }
            } catch (\Throwable $objectError) {
                throw new MuseumException(GetMessage('MUSEUM_DATABASE_WRONG_OBJECT', [
                    '#ERROR_CODE#' => $objectError->getCode(),
                    '#ERROR_MESSAGE#' => $objectError->getMessage(),
                    '#FIELD#' => $museumObjectCode,
                    '#ROW#' => sprintf('%d', $rowNumber),
                ]), $databaseFile->getBasename(), $objectError);
            }

            $phoneKey = '~'.$phone;
            $birthdayKey = '~'.Date::toFormat($birthday, DateFormat::DB_DATE);
            $identifierKey = '~'.$identifier;

            $this->museumDatabaseData[$phoneKey] ??= [];
            $this->museumDatabaseData[$phoneKey][$birthdayKey] ??= [];
            $this->museumDatabaseData[$phoneKey][$birthdayKey][$identifierKey] = $statusIndex;
        }

        $found->finalize();

        return $this->museumDatabaseData;
    }

    /**
     * @return MuseumObject[]
     */
    private function getMuseumObjects(): array
    {
        return $this->museumObjectCache ??= $this->museumObjects->getActiveMuseumObjects();
    }

    /**
     * Возвращает структуру [~пользователь⇾[~посещение⇾[~объект⇾посещение]]].
     *
     * @return array<int,array<string,array<int,MuseumVisit>>>
     *
     * @throws MuseumException
     */
    private function getSavedState(): array
    {
        if (null !== $this->savedStatus) {
            return $this->savedStatus;
        }

        $this->savedStatus = [];

        foreach ($this->museumVisits->getAllVisits() as $visit) {
            $userKey = $visit->museumVisitUserIdentifier;
            $visitKey = '~'.$visit->museumVisitVisit;
            $objectKey = $visit->museumVisitObjectIdentifier;
            $this->savedStatus[$userKey] ??= [];
            $this->savedStatus[$userKey][$visitKey] ??= [];
            $this->savedStatus[$userKey][$visitKey][$objectKey] = $visit;
        }

        return $this->savedStatus;
    }

    /**
     * Возвращает структуру [~телефон⇾[~дата⇾идентификатор]].
     *
     * @return array<string,array<string,int>>
     *
     * @throws MuseumException
     */
    private function getUserIndex(): array
    {
        if (null !== $this->userIndex) {
            return $this->userIndex;
        }

        $this->userIndex = [];

        foreach ($this->users->findActiveUsers() as $user) {
            $phoneKey = '~'.(Phone::tryNormalizePhone($user->phone) ?? $user->phone);
            $birthdayKey = '~'.(null === $user->birthday ? '' : Date::toFormat($user->birthday, DateFormat::DB_DATE));

            $identifier = $this->userIndex[$phoneKey][$birthdayKey] ?? null;

            if (null !== $identifier) {
                throw new MuseumException(GetMessage('DUPLICATE_PHONE_BIRTHDAY_USERS', [
                    '#USERS#' => sprintf('%d,%d', $identifier, $user->userIdentifier),
                ]), sprintf('%d', $identifier));
            }

            $this->userIndex[$phoneKey] ??= [];
            $this->userIndex[$phoneKey][$birthdayKey] = $user->userIdentifier;
        }

        return $this->userIndex;
    }
}
