<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\QuestionToApiConverter;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\QuestionManager;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetQuestion
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly QuestionToApiConverter $converter,
        private readonly QuestionManager $questions,
    ) {}

    #[Route(pattern: '~^/api/user/get\-question/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $identifier = filter_var($id, FILTER_VALIDATE_INT);

        if (!\is_int($identifier)) {
            throw new NotFoundError();
        }

        try {
            $question = $this->questions->getUserQuestion($user, $identifier);
        } catch (NotFoundException) {
            throw new NotFoundError();
        }

        $questionIndex = $this->converter->convertQuestionsToApi($question);

        return [
            'question' => $questionIndex[$question],
        ];
    }
}
