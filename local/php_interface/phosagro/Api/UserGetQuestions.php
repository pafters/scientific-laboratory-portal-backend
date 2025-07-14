<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Converter\QuestionToApiConverter;
use Phosagro\Manager\QuestionManager;
use Phosagro\Object\Question;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class UserGetQuestions
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly QuestionToApiConverter $converter,
        private readonly QuestionManager $questions,
    ) {}

    #[Route(pattern: '~^/api/user/get\-questions/(?:\?.*)?$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        $page = \is_int($page) ? max(1, $page) : 1;

        $questionList = $this->questions->getUserQuestions($user, $page);

        $questionIndex = $this->converter->convertQuestionsToApi($questionList);

        return [
            'questions' => array_map(
                static fn (Question $question): array => $questionIndex[$question],
                $questionList,
            ),
        ];
    }
}
