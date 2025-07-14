<?php

declare(strict_types=1);

namespace Phosagro\Rating;

use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Object\Bitrix\User;

final class RatingToApiConverter
{
    public function __construct(
        private readonly UserManager $users,
    ) {}

    public function convertRatingResultToApi(RatingResult $result): array
    {
        $userIndex = $this->findAllUsers($result);

        return [
            'event' => $this->convertRatingTableToApi($result->event, $userIndex),
            'month' => $this->convertRatingTableToApi($result->month, $userIndex),
            'total' => $this->convertRatingTableToApi($result->total, $userIndex),
            'week' => $this->convertRatingTableToApi($result->week, $userIndex),
        ];
    }

    /**
     * @param array<int,User> $userIndex
     */
    private function convertRatingRowToApi(RatingRow $row, array $userIndex): array
    {
        return [
            'score' => $row->item->ratingScore,
            'name' => $userIndex[$row->item->userIdentifier]?->login ?? '',
            'place' => $row->place,
        ];
    }

    /**
     * @param array<int,User> $userIndex
     */
    private function convertRatingTableToApi(RatingTable $table, array $userIndex): array
    {
        /** @var array[] $top */
        $top = [];

        foreach ($table->top as $row) {
            $top[] = $this->convertRatingRowToApi($row, $userIndex);
        }

        return [
            'of' => $table->of,
            'place' => $table->place,
            'top' => $top,
        ];
    }

    /**
     * @return array<int,User>
     */
    private function findAllUsers(RatingResult $result): array
    {
        /** @var array<int,User> $userIndex */
        $userIndex = [];

        /** @var array<int,null> $userIdentifierIndex */
        $userIdentifierIndex = [];

        foreach ([$result->event, $result->month, $result->total, $result->week] as $table) {
            foreach ($table->top as $row) {
                $userIdentifierIndex[$row->item->userIdentifier] = null;
            }
        }

        $userIdentifierList = array_keys($userIdentifierIndex);

        sort($userIdentifierList, SORT_NUMERIC);

        foreach ($this->users->findUsers(['=ID' => $userIdentifierList]) as $user) {
            $userIndex[$user->userIdentifier] = $user;
        }

        return $userIndex;
    }
}
