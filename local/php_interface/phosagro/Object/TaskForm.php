<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskForm
{
    /** @var array<int,TaskFormField> */
    private array $index = [];

    /** @var \SplObjectStorage<TaskFormField,null> */
    private \SplObjectStorage $marks;

    /**
     * @param TaskFormField[] $formFields
     */
    public function __construct(
        public readonly int $formIdentifier,
        public readonly array $formFields,
    ) {
        $this->marks = new \SplObjectStorage();

        foreach ($formFields as $field) {
            $this->index[$field->fieldIdentifier] = $field;
            $this->marks->attach($field);
        }
    }

    public function findField(int $fieldIdentifier): ?TaskFormField
    {
        return $this->index[$fieldIdentifier] ?? null;
    }

    /**
     * @return TaskFormField[]
     */
    public function findUnmarked(): array
    {
        return iterator_to_array($this->marks);
    }

    public function markFound(TaskFormField $field): void
    {
        $this->marks->detach($field);
    }
}
