<?php

declare(strict_types=1);

namespace Phosagro\Object;

use Phosagro\Util\Text;

final class TaskFormField
{
    /** @var array<int,TaskFormFieldVariant> */
    private array $index = [];

    /** @var \SplObjectStorage<TaskFormFieldVariant,null> */
    private \SplObjectStorage $marks;

    /**
     * @param string[]               $correctAnswers
     * @param TaskFormFieldVariant[] $fieldVariants
     */
    public function __construct(
        public readonly int $answerIdentifier,
        public readonly array $correctAnswers,
        public readonly int $fieldIdentifier,
        public readonly string $fieldSid,
        public readonly int $fieldSort,
        public readonly string $fieldTitle,
        public readonly TaskFormFieldType $fieldType,
        public readonly array $fieldVariants = [],
    ) {
        $this->marks = new \SplObjectStorage();

        foreach ($fieldVariants as $variant) {
            $this->index[$variant->variantIdentifier] = $variant;
            $this->marks->attach($variant);
        }
    }

    /**
     * @return TaskFormFieldVariant[]
     */
    public function findUnmarked(): array
    {
        return iterator_to_array($this->marks);
    }

    public function findVariant(int $variantIdentifier): ?TaskFormFieldVariant
    {
        return $this->index[$variantIdentifier] ?? null;
    }

    public function markFound(TaskFormFieldVariant $variant): void
    {
        $this->marks->detach($variant);
    }

    public static function normalizeAnswer(string $text): string
    {
        return Text::upper(Text::replace(Text::replace($text, '~,~', '.'), '~[^.:\w]~u'));
    }
}
