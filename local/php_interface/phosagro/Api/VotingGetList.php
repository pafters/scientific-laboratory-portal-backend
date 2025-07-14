<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\VotingToApiConverter;
use Phosagro\Manager\VotingManager;
use Phosagro\Object\Voting;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class VotingGetList
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly VotingManager $votings,
        private readonly VotingToApiConverter $converter,
    ) {}

    #[Route(pattern: '~^/api/voting/get\-list/(?:\?.*)?$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

        if (null === $page) {
            $page = 1;
        }

        if (!\is_int($page)) {
            return ['votings' => []];
        }

        if ($page < 1) {
            return ['votings' => []];
        }

        $found = $this->votings->getUserVotingsForList($user, $page);

        $data = $this->converter->convertVotingListToApi($found);

        return [
            'votings' => array_map(static fn (Voting $voting): array => $data[$voting], $found),
        ];
    }
}
