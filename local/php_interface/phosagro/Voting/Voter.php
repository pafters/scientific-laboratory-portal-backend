<?php

declare(strict_types=1);

namespace Phosagro\Voting;

use Bitrix\Main\Error;
use Bitrix\Vote\QuestionTable;
use Bitrix\Vote\Vote;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Voting;
use Phosagro\System\Api\Errors\ServerError;

final class Voter
{
    /**
     * @param int[] $chosenVariantIdentifierList
     */
    public function vote(User $user, Voting $voting, array $chosenVariantIdentifierList): void
    {
        $questionIdentifier = $voting->votingQuestionIdentifier;

        $foundQuestion = QuestionTable::getList([
            'filter' => [
                '=VOTE_ID' => $questionIdentifier,
            ],
            'select' => [
                'ID',
                'VOTE_ID',
            ],
        ]);

        $firstQuestion = $foundQuestion->fetchRaw();

        if (!$firstQuestion) {
            throw new \RuntimeException(sprintf('Not found question "%d".', $questionIdentifier));
        }

        if ($foundQuestion->fetchRaw()) {
            throw new \RuntimeException(sprintf('Found more than one question "%d".', $questionIdentifier));
        }

        $questionIdentifier = (int) $firstQuestion['ID'];

        $vote = new Vote((int) $firstQuestion['VOTE_ID']);

        if ($vote->isVotedFor($user->userIdentifier)) {
            throw new ServerError();
        }

        $canVote = $vote->canVote($user->userIdentifier);

        if (!$canVote->isSuccess()) {
            throw new ServerError($canVote->getErrorMessages());
        }

        $success = $vote->voteFor(
            [
                'bx_vote_event' => [
                    $vote->getId() => [
                        'BALLOT' => [
                            $questionIdentifier => $chosenVariantIdentifierList,
                        ],
                    ],
                ],
            ],
        );

        if (!$success) {
            throw new ServerError(array_map(static fn (Error $e): string => $e->getMessage(), $vote->getErrors()));
        }
    }
}
