<?php

declare(strict_types=1);

namespace Phosagro\Migration;

use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\UserFieldTable;
use Phosagro\Util\Text;

use function Phosagro\get_bitrix_error;

final class UserFieldHelper
{
    private readonly \CUserTypeEntity $fieldManager;

    public function __construct(
        private readonly DatabaseHelper $databaseHelper,
        private readonly HighloadblockHelper $highloadblockHelper,
        private readonly IblockHelper $iblocks,
    ) {
        $this->fieldManager = new \CUserTypeEntity();
    }

    public function createUserFieldBoolean(
        string $entity,
        string $field,
        string $nameEn = '',
        string $nameRu = '',
        string $helpEn = '',
        string $helpRu = '',
    ): void {
        $this->createUserField(
            $entity,
            $field,
            'boolean',
            $nameEn,
            $nameRu,
            $helpEn,
            $helpRu,
            [
                'DEFAULT_VALUE' => '0',
                'DISPLAY' => 'CHECKBOX',
                'LABEL' => ['нет', 'да'],
                'LABEL_CHECKBOX' => 'да',
            ],
        );
    }

    public function createUserFieldDatetime(
        string $entity,
        string $field,
        string $nameEn = '',
        string $nameRu = '',
        string $helpEn = '',
        string $helpRu = '',
    ): void {
        $this->createUserField(
            $entity,
            $field,
            'datetime',
            $nameEn,
            $nameRu,
            $helpEn,
            $helpRu,
            [
                'DEFAULT_VALUE' => ['TYPE' => 'NONE', 'VALUE' => ''],
                'USE_SECOND' => 'Y',
                'USE_TIMEZONE' => 'N',
            ],
        );
    }

    public function createUserFieldElement(
        string $entity,
        string $field,
        string $linkType,
        string $linkIblock,
        string $nameEn = '',
        string $nameRu = '',
        string $helpEn = '',
        string $helpRu = '',
    ): void {
        $this->createUserField(
            $entity,
            $field,
            'iblock_element',
            $nameEn,
            $nameRu,
            $helpEn,
            $helpRu,
            [
                'ACTIVE_FILTER' => '',
                'DEFAULT_VALUE' => '0',
                'DISPLAY' => 'LIST',
                'IBLOCK_ID' => $this->iblocks->getIblockId($linkType, $linkIblock),
                'IBLOCK_TYPE_ID' => $linkType,
                'LIST_HEIGHT' => '1',
            ],
        );
    }

    public function createUserFieldInteger(
        string $entity,
        string $field,
        string $nameEn = '',
        string $nameRu = '',
        string $helpEn = '',
        string $helpRu = '',
    ): void {
        $this->createUserField(
            $entity,
            $field,
            'integer',
            $nameEn,
            $nameRu,
            $helpEn,
            $helpRu,
            [
                'DEFAULT_VALUE' => '',
                'MAX_VALUE' => '0',
                'MIN_VALUE' => '0',
                'SIZE' => '20',
            ],
        );
    }

    public function createUserFieldString(
        string $entity,
        string $field,
        string $nameEn = '',
        string $nameRu = '',
        string $helpEn = '',
        string $helpRu = '',
    ): void {
        $this->createUserField(
            $entity,
            $field,
            'string',
            $nameEn,
            $nameRu,
            $helpEn,
            $helpRu,
            [
                'DEFAULT_VALUE' => '',
                'MAX_LENGTH' => '0',
                'MIN_LENGTH' => '0',
                'REGEXP' => '',
                'ROWS' => '1',
                'SIZE' => '50',
            ],
        );
    }

    public function deleteUserField(string $entity, string $field): void
    {
        $result = $this->fieldManager->Delete($this->getUserFieldId($entity, $field));
        $this->databaseHelper->assertSuccess($result, 'userfield', "{$entity}/{$field}", 'delete');
    }

    public function getEntityForHighloadblock(string $highloadblock): string
    {
        return sprintf('HLBLOCK_%d', $this->highloadblockHelper->getHighloadblockId($highloadblock));
    }

    public function getUserFieldId(string $entity, string $field): int
    {
        return $this->databaseHelper->fetchSingleId(UserFieldTable::getList([
            'filter' => [
                '=ENTITY_ID' => $entity,
                '=FIELD_NAME' => $field,
            ],
            'limit' => 2,
            'select' => [
                'ID',
            ],
        ]), 'userfield', "{$entity}/{$field}");
    }

    private function createUserField(
        string $entity,
        string $field,
        string $type,
        string $nameEn = '',
        string $nameRu = '',
        string $helpEn = '',
        string $helpRu = '',
        array $settings = [],
    ): void {
        if ('' === $nameEn) {
            $nameEn = self::fieldTitle($field);
        }

        $error = [
            'en' => sprintf('Field "%s" is not selected.', $nameEn),
            'ru' => sprintf('Не заполнено поле "%s".', $nameRu),
        ];

        $result = $this->fieldManager->Add([
            'EDIT_FORM_LABEL' => ['en' => $nameEn, 'ru' => $nameRu],
            'EDIT_IN_LIST' => '', // пусто - нет, Y - да
            'ENTITY_ID' => $entity,
            'ERROR_MESSAGE' => $error,
            'FIELD_NAME' => $field,
            'HELP_MESSAGE' => ['en' => $helpEn, 'ru' => $helpRu],
            'IS_SEARCHABLE' => 'N',
            'LIST_COLUMN_LABEL' => ['en' => $nameEn, 'ru' => $nameRu],
            'LIST_FILTER_LABEL' => ['en' => $nameEn, 'ru' => $nameRu],
            'MANDATORY' => 'Y',
            'MULTIPLE' => 'N',
            'SETTINGS' => $settings,
            'SHOW_FILTER' => 'I', // N - нет, I - точно, E - маска, S - подстрока
            'SHOW_IN_LIST' => 'Y', // пусто - нет, Y - да
            'SORT' => $this->getNextSort($entity),
            'USER_TYPE_ID' => $type,
            'XML_ID' => $field,
        ]);

        if (!$result) {
            throw new \RuntimeException('Can not add user field. '.get_bitrix_error());
        }
    }

    private static function fieldTitle(string $field): string
    {
        return Text::title(Text::removePrefix($field, 'UF_'));
    }

    private function getNextSort(string $entity): int
    {
        return $this->databaseHelper->fetchSingleInt(UserFieldTable::getList([
            'filter' => [
                '=ENTITY_ID' => $entity,
            ],
            'select' => [
                new ExpressionField('MAX_SORT', 'max(SORT)'),
            ],
        ]), 'user field', '', 'MAX_SORT') + 10;
    }
}
