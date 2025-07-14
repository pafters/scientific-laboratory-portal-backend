<?php

declare(strict_types=1);

namespace Phosagro\Object;

final class TaskFormFieldAnswer
{
    public readonly bool $correct;

    /**
     * @param TaskFormFieldVariant[] $multiChoice
     */
    public function __construct(
        public readonly TaskFormField $answerField,
        public readonly array $multiChoice = [],
        public readonly float $numericAnswer = NAN,
        public readonly ?TaskFormFieldVariant $singleChoice = null,
        public readonly string $textAnswer = '',
    ) {
        $answered = 0;

        if ($answered > 1) {
            throw new \LogicException(sprintf('Multiple answers for the field %d.', $answerField->fieldIdentifier));
        }

        $correct = true;

        if ([] !== $answerField->correctAnswers) {
            $correct = false;

            if (TaskFormFieldType::CHECKBOX === $answerField->fieldType) {
                $correctAnswerList = $answerField->correctAnswers;

                /** @var string[] $answerList */
                $answerList = [];
                foreach ($this->multiChoice as $variant) {
                    $answerList[] = $answerField->normalizeAnswer($variant->variantTitle);
                }

                sort($correctAnswerList, SORT_STRING);
                sort($answerList, SORT_STRING);

                $correct = ($answerList === $correctAnswerList);
            } elseif (TaskFormFieldType::NUMBER === $answerField->fieldType) {
                foreach ($answerField->correctAnswers as $correctAnswer) {
                    $correctValue = filter_var($correctAnswer, FILTER_VALIDATE_FLOAT);
                    if (
                        \is_float($correctValue)
                        && !is_nan($this->numericAnswer)
                        && is_finite($this->numericAnswer)
                        && (abs($correctValue - $this->numericAnswer) < 0.00001)
                    ) {
                        $correct = true;

                        break;
                    }
                }
            } elseif (TaskFormFieldType::RADIO === $answerField->fieldType) {
                if (null !== $this->singleChoice) {
                    $answer = $answerField->normalizeAnswer($this->singleChoice->variantTitle);
                    $correct = \in_array($answer, $answerField->correctAnswers, true);
                }
            } elseif (TaskFormFieldType::TEXT === $answerField->fieldType) {
                $answer = $answerField->normalizeAnswer($this->textAnswer);
                $correct = \in_array($answer, $answerField->correctAnswers, true);
            }
        }

        $this->correct = $correct;
    }
}
