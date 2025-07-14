<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Event\Status\StatusRetriever;
use Phosagro\Event\Task\Completeoner;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Manager\TaskManager;
use Phosagro\Object\TaskFile;
use Phosagro\Object\TaskFormFieldAnswer;
use Phosagro\Object\TaskFormFieldType;
use Phosagro\Object\TaskFormFieldVariant;
use Phosagro\Object\TaskStatus;
use Phosagro\Object\TaskType;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\NotAccessibleError;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\NotFoundError;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Errors\TaskUnavailableError;
use Phosagro\System\Api\Route;
use Phosagro\User\AuthorizationContext;

final class TaskComplete
{
    private const ANSWERS = 'answers';
    private const CONTENT = 'content';
    private const FILES = 'files';
    private const NAME = 'name';

    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly AuthorizationContext $authorization,
        private readonly Completeoner $completeoner,
        private readonly ParticipantManager $participants,
        private readonly StatusRetriever $statuses,
        private readonly TaskManager $tasks,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/task/complete/(?<id>[^/]+)/$~')]
    public function execute(string $id): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $taskIdentifier = filter_var($id, FILTER_VALIDATE_INT);

        if (!\is_int($taskIdentifier)) {
            throw new NotFoundError();
        }

        $task = $this->tasks->findFirstElement(['ACTIVE' => 'Y', 'ID' => $taskIdentifier]);

        if (null === $task) {
            throw new NotFoundError();
        }

        $participant = $this->participants->findFirstElement([
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'PROPERTY_EVENT' => $task->eventIdentifier,
            'PROPERTY_USER' => $user->userIdentifier,
        ]);

        if (null === $participant) {
            throw new NotAccessibleError();
        }

        $taskList = $this->tasks->findAllElements(['ACTIVE' => 'Y', 'PROPERTY_EVENT' => $task->eventIdentifier]);

        /*
         * Для получения статуса заменяем task на такой же объект только из taskList.
         * Это костыль, но решается он или полноценной ORM, что не реально,
         * или получением объектов по идентификаторам, что нормально, но раздувает размер кода.
         */
        foreach ($taskList as $eventTask) {
            if ($eventTask->taskIdentifier === $task->taskIdentifier) {
                $task = $eventTask;

                break;
            }
        }

        $this->statuses->loadTaskStatus($taskList, $user);

        $status = $this->statuses->retrieveTaskStatus($task, $user);

        if (TaskStatus::AVAILABLE !== $status) {
            throw new TaskUnavailableError($this->statuses->retrieveTaskStatusReason($task, $user));
        }

        /** @var TaskFormFieldAnswer[] $answerList */
        $answerList = [];

        $accessor = $this->accessors->createFromRequest();

        if (TaskType::FILL_OUT_THE_FORM === $task->taskType) {
            if (null === $task->formData) {
                throw new ServerError([GetMessage('TASK_WITHOUT_FORM')]);
            }
            $accessor->assertObject(self::ANSWERS, true);
            $accessor->checkErrors();
            $answersAccessor = $accessor->getObject(self::ANSWERS, true);
            foreach ($answersAccessor->getKeys() as $key) {
                $fieldIdentifier = filter_var($key, FILTER_VALIDATE_INT);
                if (!\is_int($fieldIdentifier)) {
                    $answersAccessor->addErrorUnexpected($key);

                    continue;
                }
                $field = $task->formData->findField($fieldIdentifier);
                if (null === $field) {
                    $answersAccessor->addErrorUnexpected($key);

                    continue;
                }
                $task->formData->markFound($field);
                if (TaskFormFieldType::CHECKBOX === $field->fieldType) {
                    $answersAccessor->assertObject($key, true);
                    if (!$answersAccessor->hasErrors()) {
                        $choiceAccessor = $answersAccessor->getObject($key, true);
                        foreach ($choiceAccessor->getKeys() as $choiceKey) {
                            $choiceAccessor->assertIntParsed($choiceKey);
                        }
                        if (!$choiceAccessor->hasErrors()) {
                            /** @var array<int,TaskFormFieldVariant> $choiceIndex */
                            $choiceIndex = [];
                            foreach ($choiceAccessor->getKeys() as $choiceKey) {
                                $choiceIdentifier = $choiceAccessor->getIntParsed($choiceKey);
                                $variant = $field->findVariant($choiceIdentifier);
                                if (null === $variant) {
                                    $choiceAccessor->addErrorUnexpected($choiceKey);

                                    continue;
                                }
                                $field->markFound($variant);
                                $choiceIndex[$choiceIdentifier] = $variant;
                            }
                            $answerList[] = new TaskFormFieldAnswer($field, multiChoice: array_values($choiceIndex));
                        }
                    }
                } elseif (TaskFormFieldType::NUMBER === $field->fieldType) {
                    $answersAccessor->assertFloatParsed($key);
                    if (!$answersAccessor->hasErrors()) {
                        $numericAnswer = $answersAccessor->getFloatParsed($key);
                        $answerList[] = new TaskFormFieldAnswer($field, numericAnswer: $numericAnswer);
                    }
                } elseif (TaskFormFieldType::RADIO === $field->fieldType) {
                    $answersAccessor->assertIntParsed($key);
                    if (!$answersAccessor->hasErrors()) {
                        $choiceIdentifier = $answersAccessor->getIntParsed($key);
                        $variant = $field->findVariant($choiceIdentifier);
                        if (null === $variant) {
                            $answersAccessor->addErrorUnexpected($key);

                            continue;
                        }
                        $field->markFound($variant);
                        $answerList[] = new TaskFormFieldAnswer($field, singleChoice: $variant);
                    }
                } elseif (TaskFormFieldType::TEXT === $field->fieldType) {
                    $answersAccessor->assertStringFilled($key);
                    if (!$answersAccessor->hasErrors()) {
                        $textAnswer = $answersAccessor->getStringFilled($key);
                        $answerList[] = new TaskFormFieldAnswer($field, textAnswer: $textAnswer);
                    }
                } else {
                    throw new \LogicException(sprintf('Unknown task form field type "%s".', $field->fieldType->name));
                }
            }
            foreach ($task->formData->findUnmarked() as $unmarked) {
                $answersAccessor->addErrorRequired(sprintf('%d', $unmarked->fieldIdentifier));
            }
        } else {
            $accessor->assertMissing(self::ANSWERS);
        }

        /** @var TaskFile[] $fileList */
        $fileList = [];

        if (TaskType::UPLOAD_FILE === $task->taskType) {
            $filesData = $task->filesData;
            $accessor->assertObject(self::FILES, true);
            if (!$accessor->hasFieldError(self::FILES)) {
                $filesAccessor = $accessor->getObject(self::FILES, true);
                foreach ($filesAccessor->getKeys() as $fileKey) {
                    $filesAccessor->assertObject($fileKey);
                    if (!$filesAccessor->hasFieldError($fileKey)) {
                        $fileAccessor = $filesAccessor->getObject($fileKey);
                        $fileAccessor->assertBase64Filled(self::CONTENT);
                        $fileAccessor->assertStringFilled(self::NAME);
                        if (!$fileAccessor->hasErrors()) {
                            $name = $fileAccessor->getStringFilled(self::NAME);
                            if ([] !== $filesData->filesTypes) {
                                $extension = pathinfo($name, PATHINFO_EXTENSION);
                                if (!\in_array($extension, $filesData->filesTypes, true)) {
                                    $fileAccessor->addErrorInvalid(self::NAME);
                                }
                            }
                            if (!$fileAccessor->hasErrors()) {
                                $content = $fileAccessor->getBase64Filled(self::CONTENT);
                                $fileList[] = new TaskFile($content, $name);
                            }
                        }
                    }
                }
                if ([] === $filesAccessor->getKeys()) {
                    $accessor->addErrorRequired(self::FILES);
                }
                if ($filesData->filesCount > 0) {
                    if (\count($filesAccessor->getKeys()) > $filesData->filesCount) {
                        $accessor->addErrorExceeded(self::FILES);
                    }
                }
            }
        } else {
            $accessor->assertMissing(self::FILES);
        }

        $accessor->checkErrors();

        $this->completeoner->completeTask(
            $task,
            $participant,
            $answerList,
            $fileList,
        );

        return [];
    }
}
