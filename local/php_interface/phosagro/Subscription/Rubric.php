<?php

declare(strict_types=1);

namespace Phosagro\Subscription;

/**
 * Информация о рассылке.
 */
final class Rubric
{
    public function __construct(
        public readonly bool $rubricActive,
        public readonly string $rubricCode,
        public readonly int $rubricIdentifier,
    ) {}

    public function getKnownCode(): ?RubricCode
    {
        foreach (RubricCode::cases() as $code) {
            if ($code->name === $this->rubricCode) {
                return $code;
            }
        }

        return null;
    }
}
