<?php

declare(strict_types=1);

namespace Phosagro;

use Bitrix\Iblock\IblockTable;

final class Iblocks
{
    private static ?array $cache = null;

    public static function accrualReasonId(): int
    {
        return self::getId('AccrualReason');
    }

    public static function ageCategoryId(): int
    {
        return self::getId('AgeCategory');
    }

    public static function cityId(): int
    {
        return self::getId('City');
    }

    public static function completionId(): int
    {
        return self::getId('Completion');
    }

    public static function contactsId(): int
    {
        return self::getId('Contacts');
    }

    public static function courseId(): int
    {
        return self::getId('Course');
    }

    public static function eventId(): int
    {
        return self::getId('Event');
    }

    public static function faqId(): int
    {
        return self::getId('Faq');
    }

    public static function getIblockIdentifier(string $apiCode): int
    {
        return self::getId($apiCode);
    }

    public static function newsId(): int
    {
        return self::getId('News');
    }

    public static function obsceneWordId(): int
    {
        return self::getId('ObsceneWord');
    }

    public static function participantId(): int
    {
        return self::getId('Participant');
    }

    public static function partnerId(): int
    {
        return self::getId('Partner');
    }

    public static function phosagroCompanyId(): int
    {
        return self::getId('PhosagroCompany');
    }

    public static function questionId(): int
    {
        return self::getId('Question');
    }

    public static function questionTopicId(): int
    {
        return self::getId('QuestionTopic');
    }

    public static function taskId(): int
    {
        return self::getId('Task');
    }

    public static function taskTypeId(): int
    {
        return self::getId('TaskType');
    }

    public static function templateId(): int
    {
        return self::getId('Template');
    }

    public static function userGroupId(): int
    {
        return self::getId('UserGroup');
    }

    public static function videoId(): int
    {
        return self::getId('Video');
    }

    public static function votingId(): int
    {
        return self::getId('Voting');
    }

    private static function getId(string $apiCode): int
    {
        $id = self::getMapWithLocalCache()["~{$apiCode}"] ?? null;

        if (null === $id) {
            throw new \RuntimeException(sprintf('Not found iblock "%s".', $apiCode));
        }

        return $id;
    }

    /**
     * @return array<string,int>
     */
    private static function getMapWithBitrixCache(): array
    {
        $cache = new \CPHPCache();
        $cacheDir = '/phosagro_iblock_ids';

        if ($cache->InitCache(86400, 'phosagro_iblock_ids', $cacheDir)) {
            return $cache->GetVars();
        }

        $cache->StartDataCache();

        try {
            $result = self::loadMap();
        } catch (\Throwable $error) {
            $cache->AbortDataCache();

            throw $error;
        }

        global $CACHE_MANAGER;
        $CACHE_MANAGER->StartTagCache($cacheDir);
        $CACHE_MANAGER->RegisterTag('iblock_id_new');
        $CACHE_MANAGER->EndTagCache();

        $cache->EndDataCache($result);

        return $result;
    }

    /**
     * @return array<string,int>
     */
    private static function getMapWithLocalCache(): array
    {
        return self::$cache ??= self::getMapWithBitrixCache();
    }

    /**
     * @return array<string,int>
     */
    private static function loadMap(): array
    {
        /** @var array<string,int> $result */
        $result = [];

        $found = IblockTable::getList([
            'select' => [
                'API_CODE',
                'ID',
            ],
        ]);

        while ($row = $found->fetchRaw()) {
            $code = $row['API_CODE'];
            $id = (int) $row['ID'];
            $key = "~{$code}";

            if (\array_key_exists($key, $result)) {
                throw new \RuntimeException(sprintf('Duplicate iblock "%s"', $code));
            }

            $result[$key] = $id;
        }

        return $result;
    }
}
