<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\VotingManager;
use Phosagro\Object\VotingVariant;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;
use Phosagro\Voting\Voter;

final class VotingVote
{
    private const CHOSEN = 'chosen';

    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly AuthorizationContext $authorization,
        private readonly Voter $voter,
        private readonly VotingManager $votings,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/voting/vote/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $votingIdentifier = filter_var($id, FILTER_VALIDATE_INT);

        if (!\is_int($votingIdentifier)) {
            throw new NotFoundError();
        }

        try {
            $voting = $this->votings->getUserVotingForVoting($user, $votingIdentifier);
        } catch (NotFoundException) {
            throw new NotFoundError();
        }

        $variantIdentifierList = array_values(array_map(
            static fn (VotingVariant $v): string => sprintf('%d', $v->variantIdentifier),
            $voting->votingVariantList
        ));

        /** @var array<int,null> $chosenVariantIdentifierIndex */
        $chosenVariantIdentifierIndex = [];

        $accessor = $this->accessors->createFromRequest();
        $accessor->assertArray(self::CHOSEN);
        if (!$accessor->hasFieldError(self::CHOSEN)) {
            $chosenAccessor = $accessor->getArray(self::CHOSEN);
            foreach ($chosenAccessor->getKeys() as $key) {
                $chosenAccessor->assertString($key);
                if (!$chosenAccessor->hasFieldError($key)) {
                    $variantIdentifier = $chosenAccessor->getString($key);
                    if (!\in_array($variantIdentifier, $variantIdentifierList, true)) {
                        $accessor->addErrorUnexpected(self::CHOSEN);

                        break;
                    }
                    if (\array_key_exists($variantIdentifier, $chosenVariantIdentifierIndex)) {
                        $accessor->addErrorDuplicate(self::CHOSEN);

                        break;
                    }
                    $chosenVariantIdentifierIndex[$variantIdentifier] = null;
                }
            }
            if ([] === $chosenVariantIdentifierIndex) {
                $accessor->addErrorRequired(self::CHOSEN);
            }
            if (\count($chosenVariantIdentifierIndex) > $voting->votingVariantLimit) {
                $accessor->addErrorExceeded(self::CHOSEN);
            }
        }
        $accessor->checkErrors();

        try {
            $this->voter->vote($user, $voting, array_keys($chosenVariantIdentifierIndex));
        } catch (NotFoundException) {
            throw new NotFoundError();
        }

        return [];
    }
}
