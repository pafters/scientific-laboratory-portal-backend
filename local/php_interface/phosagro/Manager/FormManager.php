<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Bitrix\Main\DB\Connection;
use Phosagro\Object\TaskForm;
use Phosagro\Object\TaskFormField;
use Phosagro\Object\TaskFormFieldAnswer;
use Phosagro\Object\TaskFormFieldType;
use Phosagro\Object\TaskFormFieldVariant;
use Phosagro\Util\Text;

final class FormManager
{
    public function __construct(
        private readonly Connection $database,
    ) {}

    public function findForm(int $formIdentifier): ?TaskForm
    {
        $rowForm = \CForm::GetByID($formIdentifier)->Fetch();

        if (!$rowForm) {
            return null;
        }

        /** @var TaskFormField[] $fieldList */
        $fieldList = [];

        $foundFields = \CFormField::GetList($formIdentifier, 'N');

        while ($rowField = $foundFields->Fetch()) {
            /*
             * В битриксе нет числового типа поля.
             * Считаем числовым любое текстовое поле с числовым валидатором.
             */

            $fieldIdentifier = (int) $rowField['ID'];

            $hasNumberValidator = false;

            $foundValidator = \CFormValidator::GetList($fieldIdentifier);

            while ($rowValidator = $foundValidator->Fetch()) {
                if (('number' === $rowValidator['NAME']) || ('number_ext' === $rowValidator['NAME'])) {
                    $hasNumberValidator = true;

                    break;
                }
            }

            /*
             * В битриксе невозможно указать правильный вариант ответа для поля веб-формы.
             * Считаем правильными ответы из строк комментария совпадающих с шаблоном:
             * Ответ: {ОТВЕТ}
             */
            $correnctAnswerList = [];

            $commentLineList = explode("\n", (string) $rowField['COMMENTS']);

            foreach ($commentLineList as $commentLine) {
                $commentLineData = TaskFormField::normalizeAnswer($commentLine);
                $answerPrefix = 'ОТВЕТ:';
                if (str_starts_with($commentLineData, $answerPrefix)) {
                    $answerData = Text::substring($commentLineData, Text::length($answerPrefix));
                    if ('' !== $answerData) {
                        $correnctAnswerList[] = $answerData;
                    }
                }
            }

            /*
             * Тип поля хранится не в поле а в ответе.
             * Если поле не checkbox или radio то у него будет один ответ
             * из которого нужно взять тип.
             */

            $fieldType = TaskFormFieldType::TEXT;

            $answerIdentifier = 0;

            /** @var TaskFormFieldVariant[] $variantList */
            $variantList = [];

            $foundAnswer = \CFormAnswer::GetList($fieldIdentifier);

            while ($rowAnswer = $foundAnswer->Fetch()) {
                $answerIdentifier = (int) $rowAnswer['ID'];

                $fieldType = match ($rowAnswer['FIELD_TYPE']) {
                    'checkbox' => TaskFormFieldType::CHECKBOX,
                    'radio' => TaskFormFieldType::RADIO,
                    default => $hasNumberValidator ? TaskFormFieldType::NUMBER : TaskFormFieldType::TEXT,
                };

                $variantTitle = trim((string) $rowAnswer['MESSAGE']);

                if ($fieldType->hasVariants()) {
                    $variantList[] = new TaskFormFieldVariant(
                        \in_array(TaskFormField::normalizeAnswer($variantTitle), $correnctAnswerList, true),
                        $answerIdentifier,
                        (int) $rowAnswer['C_SORT'],
                        $variantTitle,
                    );
                }
            }

            $fieldTitle = trim((string) $rowField['TITLE']);
            $fieldTitleType = trim((string) $rowField['TITLE_TYPE']);
            if ('text' === $fieldTitleType) {
                $fieldTitle = htmlspecialchars($fieldTitle);
            }

            $fieldList[] = new TaskFormField(
                $answerIdentifier,
                $correnctAnswerList,
                (int) $rowField['ID'],
                (string) $rowField['SID'],
                (int) $rowField['C_SORT'],
                $fieldTitle,
                $fieldType,
                $variantList,
            );
        }

        return new TaskForm(
            $formIdentifier,
            $fieldList,
        );
    }

    /**
     * @return \WeakMap<TaskFormField,TaskFormFieldAnswer>
     */
    public function loadFormResults(TaskForm $form, int $resultIdentifier): \WeakMap
    {
        /** @var \WeakMap<TaskFormField,TaskFormFieldAnswer> $answerIndex */
        $answerIndex = new \WeakMap();

        foreach ($form->formFields as $field) {
            $answerIndex[$field] = new TaskFormFieldAnswer($field);
        }

        $found = $this->database->query(sprintf(
            <<<'SQL'
                select
                  ANSWER_ID,
                  FIELD_ID,
                  USER_TEXT
                from b_form_result_answer
                where RESULT_ID = %d
                  and FORM_ID = %d
                SQL,
            $resultIdentifier,
            $form->formIdentifier,
        ));

        while ($row = $found->fetchRaw()) {
            $field = $form->findField((int) $row['FIELD_ID']);

            if (null === $field) {
                continue;
            }

            if (TaskFormFieldType::CHECKBOX === $field->fieldType) {
                $variant = $field->findVariant((int) $row['ANSWER_ID']);

                if (null !== $variant) {
                    $multiChoice = $answerIndex[$field]->multiChoice;
                    $multiChoice[] = $variant;
                    $answerIndex[$field] = new TaskFormFieldAnswer($field, multiChoice: $multiChoice);
                }

                continue;
            }

            if (TaskFormFieldType::NUMBER === $field->fieldType) {
                $value = filter_var((string) $row['USER_TEXT'], FILTER_VALIDATE_FLOAT);

                if (\is_float($value)) {
                    $answerIndex[$field] = new TaskFormFieldAnswer($field, numericAnswer: $value);
                }

                continue;
            }

            if (TaskFormFieldType::RADIO === $field->fieldType) {
                $variant = $field->findVariant((int) $row['ANSWER_ID']);

                if (null !== $variant) {
                    $answerIndex[$field] = new TaskFormFieldAnswer($field, singleChoice: $variant);
                }

                continue;
            }

            if (TaskFormFieldType::TEXT === $field->fieldType) {
                $answerIndex[$field] = new TaskFormFieldAnswer($field, textAnswer: (string) $row['USER_TEXT']);

                continue;
            }
        }

        return $answerIndex;
    }
}
