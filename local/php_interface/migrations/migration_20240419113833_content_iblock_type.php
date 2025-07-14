<?php

declare(strict_types=1);

return static function (): void {
    $en = [
        'ELEMENT_NAME' => 'Elements',
        'NAME' => 'Content',
        'SECTION_NAME' => 'Sections',
    ];

    $ru = [
        'ELEMENT_NAME' => 'Элементы',
        'NAME' => 'Контент',
        'SECTION_NAME' => 'Разделы',
    ];

    $manager = new CIBlockType();
    $result = $manager->Add([
        'EDIT_FILE_AFTER' => '',
        'EDIT_FILE_BEFORE' => '',
        'ID' => 'content',
        'IN_RSS' => 'N',
        'LANG' => ['en' => $en, 'ru' => $ru],
        'SECTIONS' => 'Y',
        'SORT' => 10,
    ]);

    if (!$result) {
        throw new RuntimeException('Can not create content iblock type. '.Phosagro\get_bitrix_error());
    }
};
