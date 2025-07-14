<?php

declare(strict_types=1);

namespace Phosagro\Event\Task;

use Phosagro\Manager\FormManager;
use Phosagro\Object\Completion;
use Phosagro\Object\Task;
use Phosagro\Object\TaskType;

final class CompletionChecker
{
    public function __construct(
        private readonly FormManager $forms,
    ) {}

    public function checkCompletion(Completion $completion, Task $task): CompletionStatus
    {
        if (TaskType::FILL_OUT_THE_FORM === $task->taskType) {
            if (null === $task->formData) {
                return CompletionStatus::REJECTED_BY_MODERATOR_ERROR;
            }

            $correctAnswerLimit = $task->correctAnswerLimit ?? \count($task->formData->formFields);
            $correctCount = 0;

            $answers = $this->forms->loadFormResults($task->formData, $completion->answerIdentifier);

            foreach ($task->formData->formFields as $field) {
                if ($answers[$field]->correct) {
                    ++$correctCount;
                }
            }

            if ($correctCount < $correctAnswerLimit) {
                return CompletionStatus::REJECTED_BY_WRONG_ANSWERS;
            }
        }

        if (TaskType::UPLOAD_FILE === $task->taskType) {
            if (!$completion->completionActive && $completion->filesRejeted) {
                return CompletionStatus::REJECTED_BY_WRONG_FILES;
            }
        }

        if ($completion->completionActive) {
            return CompletionStatus::ACCEPTED;
        }

        return CompletionStatus::MODERATING;
    }
}
