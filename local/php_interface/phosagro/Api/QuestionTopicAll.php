<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Manager\QuestionTopicManager;
use Phosagro\Object\QuestionTopic;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class QuestionTopicAll
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly QuestionTopicManager $questionTopics,
    ) {}

    #[Route(pattern: '~^/api/question\-topic/all/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        return ['topics' => array_map(
            static fn (QuestionTopic $t): array => $t->toApi(),
            $this->questionTopics->findAll(),
        )];
    }
}
