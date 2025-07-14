<?php

declare(strict_types=1);

return static function (): void {
    $en = [
        'ELEMENT_NAME' => 'Elements',
        'NAME' => 'Directores',
        'SECTION_NAME' => 'Sections',
    ];

    $ru = [
        'ELEMENT_NAME' => 'Элементы',
        'NAME' => 'Справочники',
        'SECTION_NAME' => 'Разделы',
    ];

    $manager = new CIBlockType();
    $result = $manager->Add([
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ID' => 'directory',
        'IN_RSS' => 'N',
        'LANG' => ['en' => $en, 'ru' => $ru],
        'SECTIONS' => 'N',
        'SORT' => 20,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create directory iblock type. '.Phosagro\get_bitrix_error());
    }
};
