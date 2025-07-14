<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;
use Phosagro\Migration\IblockPropertyEnumHelper;
use Phosagro\Migration\IblockPropertyHelper;
use Phosagro\Migration\IblockTypeHelper;

return static function (
    IblockHelper $iblocks,
    IblockPropertyEnumHelper $enums,
    IblockPropertyHelper $properties,
    IblockTypeHelper $types,
): void {
    $types->createIblockType('questions', 'Questions', 'Обращения');

    $iblocks->createIblock('questions', 'Question', 'Обращение', 'Обращение', 'Обращения', [
        'FIELDS' => [
            'LOG_ELEMENT_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_ELEMENT_EDIT' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_ADD' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_DELETE' => ['IS_REQUIRED' => 'Y'],
            'LOG_SECTION_EDIT' => ['IS_REQUIRED' => 'Y'],
            'PREVIEW_TEXT' => ['IS_REQUIRED' => 'Y'],
        ],
    ]);

    $properties->createPropertyUser('questions', 'Question', 'MODERATOR', 'Модератор', true);

    $properties->createPropertyUser('questions', 'Question', 'USER', 'Пользователь', true);

    $properties->createPropertyEnum('questions', 'Question', 'TYPE', 'Тип обращения', true);

    $enums->createIblockPropertyEnum('questions', 'Question', 'TYPE', 'EVENT', 'Вопрос по событию');

    $enums->createIblockPropertyEnum('questions', 'Question', 'TYPE', 'CONTENT', 'Вопрос по сайту');

    $enums->createIblockPropertyEnum('questions', 'Question', 'TYPE', 'TECHNICAL', 'Сообщение о технической проблеме');

    $properties->createPropertyElement('questions', 'Question', 'EVENT', 'Событие', 'content', 'Event');

    $properties->createPropertyElement('questions', 'Question', 'TOPIC', 'Тема вопроса', 'directory', 'QuestionTopic', true);

    $properties->createPropertyString('questions', 'Question', 'URL', 'Ссылка на страницу');

    $properties->createPropertyFile('questions', 'Question', 'FILE', 'Прикреплённый файл');
};
