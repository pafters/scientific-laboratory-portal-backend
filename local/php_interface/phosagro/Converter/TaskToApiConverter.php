<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Event\Status\StatusRetriever;
use Phosagro\Object\Bitrix\User;
use Phosagro\Object\Task;
use Phosagro\Object\TaskFiles;
use Phosagro\Object\TaskForm;
use Phosagro\Object\TaskFormField;
use Phosagro\Object\TaskFormFieldType;
use Phosagro\Object\TaskFormFieldVariant;
use Phosagro\Object\TaskPlace;
use Phosagro\Object\TaskType;
use Phosagro\Object\TaskVideo;

final class TaskToApiConverter
{
    public function __construct(
        private readonly DateTimeToApiConverter $dateTimeConverter,
        private readonly StatusRetriever $statuses,
    ) {}

    public function convertTaskFilesToApi(TaskFiles $files): array
    {
        $types = $files->filesTypes;

        sort($types, SORT_NATURAL);

        return [
            'count' => $files->filesCount,
            'types' => $types,
        ];
    }

    public function convertTaskFormFieldToApi(TaskFormField $field): array
    {
        $result = [
            'id' => sprintf('%d', $field->fieldIdentifier),
            'title' => $field->fieldTitle,
            'type' => $this->convertTaskFormFieldTypeToApi($field->fieldType),
        ];

        if ($field->fieldType->hasVariants()) {
            $variants = $field->fieldVariants;

            usort($variants, static function (TaskFormFieldVariant $a, TaskFormFieldVariant $b): int {
                return ($a->variantSort <=> $b->variantSort) ?: ($a->variantIdentifier <=> $b->variantIdentifier);
            });

            $result['variants'] = array_map($this->convertTaskFormFieldVariantToApi(...), $variants);
        }

        return $result;
    }

    public function convertTaskFormFieldTypeToApi(TaskFormFieldType $type): string
    {
        return match ($type) {
            TaskFormFieldType::CHECKBOX => 'checkbox',
            TaskFormFieldType::NUMBER => 'number',
            TaskFormFieldType::RADIO => 'radio',
            TaskFormFieldType::TEXT => 'text',
        };
    }

    public function convertTaskFormFieldVariantToApi(TaskFormFieldVariant $variant): array
    {
        return [
            'id' => sprintf('%d', $variant->variantIdentifier),
            'title' => $variant->variantTitle,
        ];
    }

    public function convertTaskFormToApi(TaskForm $form): array
    {
        $fields = $form->formFields;

        usort($fields, static function (TaskFormField $a, TaskFormField $b): int {
            return ($a->fieldSort <=> $b->fieldSort) ?: ($a->fieldIdentifier <=> $b->fieldIdentifier);
        });

        return [
            'fields' => array_map($this->convertTaskFormFieldToApi(...), $fields),
        ];
    }

    public function convertTaskPlaceToApi(TaskPlace $place): array
    {
        return [
            'latitude' => $place->placeLatitude,
            'longitude' => $place->placeLongitude,
        ];
    }

    public function convertTaskStatusToApi(Task $task, User $user): array
    {
        $result = [
            'code' => $this->statuses->retrieveTaskStatus($task, $user)->value,
        ];

        $reason = $this->statuses->retrieveTaskStatusReason($task, $user);

        if (null !== $reason) {
            $result['reason'] = $reason->value;
        }

        return $result;
    }

    public function convertTaskToApi(Task $task, User $user): array
    {
        $result = [
            'id' => sprintf('%d', $task->taskIdentifier),
            'name' => $task->taskName,
            'required' => $task->taskRequired,
            'status' => $this->convertTaskStatusToApi($task, $user),
            'type' => $task->taskType->value,
        ];

        if (0 !== $task->taskBonus) {
            $result['bonus'] = $task->taskBonus;
        }

        if ('' !== $task->taskDescription) {
            $result['text'] = $task->taskDescription;
        }

        if ($task->taskDuration > 0) {
            $result['duration'] = $task->taskDuration;
        }

        if (null !== $task->taskEnds) {
            $result['ends'] = $this->dateTimeConverter->convertDateTimeToApi($task->taskEnds);
        }

        if (null !== $task->taskStarts) {
            $result['starts'] = $this->dateTimeConverter->convertDateTimeToApi($task->taskStarts);
        }

        if (TaskType::UPLOAD_FILE === $task->taskType) {
            $result['files'] = $this->convertTaskFilesToApi($task->filesData);
        }

        if ((TaskType::FILL_OUT_THE_FORM === $task->taskType) && (null !== $task->formData)) {
            $result['form'] = $this->convertTaskFormToApi($task->formData);
        }

        if (TaskType::VISIT_THE_PLACE === $task->taskType) {
            $result['place'] = $this->convertTaskPlaceToApi($task->placeData);
        }

        if (TaskType::WATCH_THE_VIDEO === $task->taskType) {
            $result['video'] = $this->convertTaskVideoToApi($task->videoData);
        }

        ksort($result, SORT_STRING);

        return $result;
    }

    public function convertTaskVideoToApi(TaskVideo $video): array
    {
        $result = [];

        if ('' !== $video->videoFileUrl) {
            $result['file'] = $video->videoFileUrl;
        }

        if ('' !== $video->videoHtml) {
            $result['html'] = $video->videoHtml;
        }

        return $result;
    }
}
