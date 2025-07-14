<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Bitrix\Vote\AnswerTable;
use Bitrix\Vote\QuestionTable;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Voting;
use Phosagro\Object\VotingVariant;
use Phosagro\System\Clock;
use Phosagro\Util\Collection;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;
use Phosagro\Util\Text;

/**
 * @extends AbstractIblockElementManager<Voting>
 */
final class VotingManager extends AbstractIblockElementManager
{
    public function __construct(
        private readonly AgeCategoryManager $ageCategories,
        private readonly Clock $clock,
        private readonly GroupManager $groups,
        private readonly ParticipantManager $participants,
    ) {}

    public function getUserVotingForPage(User $user, int $votingIdentifier): Voting
    {
        return $this->getSingleElement($this->buildUserVotingsFilter($user, [
            'ID' => $votingIdentifier,
        ]));
    }

    public function getUserVotingForPosting(User $user, int $postingIdentifier): Voting
    {
        return $this->getSingleElement($this->buildUserVotingsFilter($user, [
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_POSTING' => $postingIdentifier,
        ]));
    }

    public function getUserVotingForVoting(User $user, int $votingIdentifier): Voting
    {
        return $this->getSingleElement($this->buildUserVotingsFilter($user, [
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'ID' => $votingIdentifier,
        ]));
    }

    /**
     * @return Voting[]
     */
    public function getUserVotingsForList(User $user, int $page): array
    {
        return $this->findAllElements(
            filter: $this->buildUserVotingsFilter($user, [
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
            ]),
            nav: [
                'bShowAll' => false,
                'checkOutOfRange' => true,
                'iNumPage' => $page,
                'nPageSize' => 6,
            ]
        );
    }

    /**
     * @return Voting[]
     */
    public function getVotingsWaitingForPositng(): array
    {
        return $this->findAllElements([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_POSTING' => false,
        ]);
    }

    public function linkPostingToVoting(int $postingIdentifier, int $votingIdentifier): void
    {
        \CIBlockElement::SetPropertyValuesEx($votingIdentifier, $this->getIblockId(), [
            'POSTING' => $postingIdentifier,
        ]);
    }

    protected function createFromBitrixData(array $row): Voting
    {
        return new Voting(
            'Y' === $row['ACTIVE'],
            (int) $row['PROPERTY_AGE_CATEGORY_VALUE'],
            '',
            '',
            Date::fromFormat((string) $row['ACTIVE_TO'], DateFormat::BITRIX, DateFormat::BITRIX_DATE),
            (null === $row['PROPERTY_EVENT_VALUE']) ? null : (int) $row['PROPERTY_EVENT_VALUE'],
            array_map('\intval', $row['PROPERTY_FILES_VALUE']),
            array_map('\intval', $row['PROPERTY_GROUPS_VALUE']),
            (int) $row['ID'],
            null !== $row['PROPERTY_POSTING_VALUE'],
            trim((string) $row['NAME']),
            (int) $row['PROPERTY_OWNER_VALUE'],
            (null === $row['DETAIL_PICTURE']) ? null : (int) $row['DETAIL_PICTURE'],
            (int) $row['PROPERTY_VOTING_VALUE'],
            Date::fromFormat((string) $row['ACTIVE_FROM'], DateFormat::BITRIX, DateFormat::BITRIX_DATE),
            (int) $row['SORT'],
            (null === $row['PREVIEW_PICTURE']) ? null : (int) $row['PREVIEW_PICTURE'],
            (int) $row['PROPERTY_LIMIT_VALUE'],
            [],
        );
    }

    protected function getBitrixFields(): array
    {
        return [
            'ACTIVE',
            'ACTIVE_FROM',
            'ACTIVE_TO',
            'DETAIL_PICTURE',
            'IBLOCK_ID',
            'ID',
            'NAME',
            'PREVIEW_PICTURE',
            'PROPERTY_AGE_CATEGORY',
            'PROPERTY_EVENT',
            'PROPERTY_FILES',
            'PROPERTY_GROUPS',
            'PROPERTY_LIMIT',
            'PROPERTY_OWNER',
            'PROPERTY_POSTING',
            'PROPERTY_VOTING',
            'SORT',
        ];
    }

    protected function getDefaultOrder(): array
    {
        return [
            'sort' => 'asc',
            'date_active_from' => 'desc',
            'id' => 'asc',
        ];
    }

    /**
     * @param Voting[] $elementList
     */
    protected function loadElementList(array $elementList): void
    {
        $questionIndex = $this->buildQuestionIndex(Collection::identifierList(array_map(
            static fn (Voting $v): int => $v->votingQuestionIdentifier,
            $elementList
        )));

        $variantIndex = $this->buildVariantIndex($questionIndex);

        $aaa = [];

        foreach ($questionIndex as $vfd) {
            if (\array_key_exists((int) $vfd['VOTE_ID'], $aaa)) {
                throw new \RuntimeException('dFGDFH');
            }
            $aaa[(int) $vfd['VOTE_ID']] = Text::bitrix(trim((string) $vfd['QUESTION']), trim((string) $vfd['QUESTION_TYPE']));
        }

        foreach ($elementList as $element) {
            $element->votingDescription = $aaa[$element->votingQuestionIdentifier] ?? '';
            $element->votingBrief = Text::brief($element->votingDescription);
            $element->votingVariantList = $variantIndex[$element->votingQuestionIdentifier] ?? [];
        }
    }

    /**
     * @return int[]
     */
    private function buildAgeCategoryIdentifierList(User $user): array
    {
        /** @var int[] $result */
        $result = [];

        $age = $user->calculateAge($this->clock->now());

        foreach ($this->ageCategories->findByAge($age) as $ageCategory) {
            $result[] = $ageCategory->ageCategoryIdentifier;
        }

        return $result;
    }

    /**
     * @return int[]
     */
    private function buildEventIdentifierList(User $user): array
    {
        /** @var int[] $result */
        $result = [];

        $participantList = $this->participants->findAllElements([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_REFUSED' => false,
            'PROPERTY_USER' => $user->userIdentifier,
        ], ['id' => 'asc']);

        foreach ($participantList as $participant) {
            $result[] = $participant->eventIdentifier;
        }

        return $result;
    }

    /**
     * @return int[]
     */
    private function buildGroupIdentifierList(User $user): array
    {
        /** @var int[] */
        $result = [];

        foreach ($this->groups->findGroupsForUser($user->userIdentifier) as $group) {
            $result[] = $group->groupIdentifier;
        }

        sort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * @param int[] $questionIdentifierList
     *
     * @return array<int,array>
     */
    private function buildQuestionIndex(array $questionIdentifierList): array
    {
        /** @var array<int,array> $questionIndex */
        $questionIndex = [];

        $actualQuestionIdentifierList = Collection::identifierList($questionIdentifierList);

        if ([] !== $actualQuestionIdentifierList) {
            $foundQuestion = QuestionTable::getList([
                'filter' => [
                    '=VOTE_ID' => $actualQuestionIdentifierList,
                ],
                'order' => [
                    'ID' => 'ASC',
                ],
                'select' => [
                    'ID',
                    'QUESTION',
                    'QUESTION_TYPE',
                    'VOTE_ID',
                ],
            ]);

            while ($rowQuestion = $foundQuestion->fetchRaw()) {
                $questionIndex[(int) $rowQuestion['ID']] = $rowQuestion;
            }
        }

        return $questionIndex;
    }

    private function buildUserVotingsFilter(User $user, array $filter): array
    {
        if (null === $user->birthday) {
            return ['ID' => 0];
        }

        if ($user->blocked) {
            return ['ID' => 0];
        }

        if (!$user->active) {
            return ['ID' => 0];
        }

        $ageCategoryIdentifierList = $this->buildAgeCategoryIdentifierList($user);

        if ([] === $ageCategoryIdentifierList) {
            return ['ID' => 0];
        }

        $eventIdentifierList = $this->buildEventIdentifierList($user);

        $groupIdentifierList = $this->buildGroupIdentifierList($user);

        return array_merge($filter, [
            'LOGIC' => 'AND',
            'PROPERTY_AGE_CATEGORY' => $ageCategoryIdentifierList,
            [
                'LOGIC' => 'OR',
                ['PROPERTY_EVENT' => false],
                ['PROPERTY_EVENT' => $eventIdentifierList],
            ],
            [
                'LOGIC' => 'OR',
                ['PROPERTY_GROUPS' => false],
                ['PROPERTY_GROUPS' => $groupIdentifierList],
            ],
        ]);
    }

    /**
     * @param array<int,array> $questionIndex
     *
     * @return array<int,VotingVariant[]>
     */
    private function buildVariantIndex(array $questionIndex): array
    {
        /** @var array<int,VotingVariant[]> $variantIndex */
        $variantIndex = [];

        if ([] !== $questionIndex) {
            $found = AnswerTable::getList([
                'filter' => [
                    '=ACTIVE' => 'Y',
                    '=QUESTION_ID' => array_keys($questionIndex),
                ],
                'order' => [
                    'C_SORT' => 'ASC',
                    'ID' => 'ASC',
                ],
                'select' => [
                    'C_SORT',
                    'ID',
                    'MESSAGE',
                    'MESSAGE_TYPE',
                    'QUESTION_ID',
                ],
            ]);

            while ($row = $found->fetchRaw()) {
                $variantIndex[(int) $questionIndex[(int) $row['QUESTION_ID']]['VOTE_ID']] ??= [];
                $variantIndex[(int) $questionIndex[(int) $row['QUESTION_ID']]['VOTE_ID']][] = new VotingVariant(
                    (int) $row['ID'],
                    (int) $row['C_SORT'],
                    Text::bitrix((string) $row['MESSAGE'], (string) $row['MESSAGE_TYPE']),
                );
            }
        }

        return $variantIndex;
    }
}
