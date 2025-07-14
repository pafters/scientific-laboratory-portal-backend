<?php

declare(strict_types=1);

namespace Phosagro\Manager\Bitrix;

use Phosagro\BitrixCache;
use Phosagro\Object\Bitrix\UserField;
use Phosagro\Object\Bitrix\UserFieldEnum;
use Phosagro\Util\Text;

final class UserFieldManager
{
    /** @var array<string,array<string,array<string,UserFieldEnum>>> */
    private readonly array $enumByCode;

    /** @var array<int,UserFieldEnum> */
    private readonly array $enumById;

    /** @var \WeakMap<UserFieldEnum,int> */
    private readonly \WeakMap $enumIdMap;

    private bool $enumLoaded = false;

    /** @var array<string,array<string,UserField>> */
    private readonly array $fieldByCode;

    /** @var array<int,UserField> */
    private readonly array $fieldById;

    /** @var \WeakMap<UserField,int> */
    private readonly \WeakMap $fieldIdMap;

    private bool $fieldLoaded = false;

    public function getEnumByCode(string $entity, string $field, string $enum): UserFieldEnum
    {
        $this->loadEnums();

        $entityKey = Text::upper($entity);
        $fieldKey = Text::upper($field);
        $enumKey = Text::upper($enum);

        return $this->enumByCode[$entityKey][$fieldKey][$enumKey];
    }

    public function getEnumById(int $id): UserFieldEnum
    {
        $this->loadEnums();

        return $this->enumById[$id];
    }

    public function getEnumId(UserFieldEnum $enum): int
    {
        $this->loadEnums();

        return $this->enumIdMap[$enum];
    }

    public function getEnumIdByCode(string $entity, string $field, string $enum): int
    {
        return $this->getEnumId($this->getEnumByCode($entity, $field, $enum));
    }

    public function getFieldByCode(string $entity, string $field): UserField
    {
        $this->loadFields();

        $entityKey = Text::upper($entity);
        $fieldKey = Text::upper($field);

        return $this->fieldByCode[$entityKey][$fieldKey];
    }

    public function getFieldById(int $id): UserField
    {
        $this->loadFields();

        return $this->fieldById[$id];
    }

    public function getFieldId(UserField $field): int
    {
        $this->loadFields();

        return $this->fieldIdMap[$field];
    }

    public function getFieldIdByCode(string $entity, string $field): int
    {
        return $this->getFieldId($this->getFieldByCode($entity, $field));
    }

    public function hasEnumId(UserFieldEnum $enum): bool
    {
        $this->loadEnums();

        return isset($this->enumIdMap[$enum]);
    }

    public function hasEnumWithCode(string $entity, string $field, string $enum): bool
    {
        $this->loadEnums();

        $entityKey = Text::upper($entity);
        $fieldKey = Text::upper($field);
        $enumKey = Text::upper($enum);

        return ($this->enumByCode[$entityKey][$fieldKey][$enumKey] ?? null) !== null;
    }

    public function hasEnumWithId(int $id): bool
    {
        $this->loadEnums();

        return ($this->enumById[$id] ?? null) !== null;
    }

    public function hasFieldId(UserField $field): bool
    {
        $this->loadFields();

        return isset($this->fieldIdMap[$field]);
    }

    public function hasFieldWithCode(string $entity, string $field): bool
    {
        $this->loadFields();

        $entityKey = Text::upper($entity);
        $fieldKey = Text::upper($field);

        return ($this->fieldByCode[$entityKey][$fieldKey] ?? null) !== null;
    }

    public function hasFieldWithId(int $id): bool
    {
        $this->loadFields();

        return ($this->fieldById[$id] ?? null) !== null;
    }

    private function loadEnums(): void
    {
        if ($this->enumLoaded) {
            return;
        }

        /** @var array<string,array<string,array<string,UserFieldEnum>>> $byCode */
        $byCode = [];

        /** @var array<int,UserFieldEnum> $byId */
        $byId = [];

        /** @var \WeakMap<UserFieldEnum,int> $idMap */
        $idMap = new \WeakMap();

        /** @var array<int,UserFieldEnum> $enumList */
        $enumList = BitrixCache::get('user-field-enum-map', $this->readEnums(...));

        foreach ($enumList as $id => $enum) {
            $entityKey = Text::upper($enum->field->entity);
            $fieldKey = Text::upper($enum->field->code);
            $enumKey = Text::upper($enum->code);

            $byCode[$entityKey][$fieldKey][$enumKey] = $enum;
            $byId[$id] = $enum;
            $idMap[$enum] = $id;
        }

        $this->enumByCode = $byCode;
        $this->enumById = $byId;
        $this->enumIdMap = $idMap;
        $this->enumLoaded = true;
    }

    private function loadFields(): void
    {
        if ($this->fieldLoaded) {
            return;
        }

        /** @var array<string,array<string,UserField>> $byCode */
        $byCode = [];

        /** @var array<int,UserField> $byId */
        $byId = [];

        /** @var \WeakMap<UserField,int> $idMap */
        $idMap = new \WeakMap();

        /** @var array<int,UserField> $fieldList */
        $fieldList = BitrixCache::get('user-field-map', $this->readFields(...));

        foreach ($fieldList as $id => $field) {
            $entityKey = Text::upper($field->entity);
            $fieldKey = Text::upper($field->code);

            $byCode[$entityKey][$fieldKey] = $field;
            $byId[$id] = $field;
            $idMap[$field] = $id;
        }

        $this->fieldByCode = $byCode;
        $this->fieldById = $byId;
        $this->fieldIdMap = $idMap;
        $this->fieldLoaded = true;
    }

    /**
     * @return array<int,UserFieldEnum>
     */
    private function readEnums(): array
    {
        /** @var array<int,UserFieldEnum> $enumList */
        $enumList = [];

        $found = \CUserFieldEnum::GetList();

        while ($row = $found->Fetch()) {
            $enumList[(int) $row['ID']] = new UserFieldEnum(
                $row['XML_ID'],
                $this->getFieldById((int) $row['USER_FIELD_ID']),
                $row['VALUE'],
            );
        }

        return $enumList;
    }

    /**
     * @return array<int,UserField>
     */
    private function readFields(): array
    {
        /** @var array<int,UserField> $fieldList */
        $fieldList = [];

        $found = \CUserTypeEntity::GetList();

        while ($row = $found->Fetch()) {
            $fieldList[(int) $row['ID']] = new UserField(
                $row['FIELD_NAME'],
                $row['ENTITY_ID'],
            );
        }

        return $fieldList;
    }
}
