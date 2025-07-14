<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\VotingToApiConverter;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\VotingManager;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class VotingGetOne
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly VotingManager $votings,
        private readonly VotingToApiConverter $converter,
    ) {}

    #[Route(pattern: '~^/api/voting/get\-one/(?<id>[^/]+)/$~')]
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
            $voting = $this->votings->getUserVotingForPage($user, $votingIdentifier);
        } catch (NotFoundException) {
            throw new NotFoundError();
        }

        return [
            'voting' => $this->converter->convertVotingToApi($voting),
        ];
    }
}
