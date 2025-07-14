<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class Faq
{
    public function __construct(
        public readonly string $faqAnswerHtml,
        public readonly int $faqIdentifier,
        public readonly string $faqQuestionHtml,
    ) {}

    public function toApi(): array
    {
        return [
            'answer' => $this->faqAnswerHtml,
            'question' => $this->faqQuestionHtml,
        ];
    }
}
