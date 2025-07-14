<?php

declare(strict_types=1);

use Phosagro\Migration\IblockHelper;
use Phosagro\Migration\IblockPropertyEnumHelper;
use Phosagro\Migration\IblockPropertyHelper;
use Phosagro\Migration\IblockTypeHelper;

return static function (
    IblockHelper $i,
    IblockPropertyEnumHelper $e,
    IblockPropertyHelper $p,
    IblockTypeHelper $t,
): void {
    $t->createIblockType('event', 'Events', 'События');

    $i->createIblock('event', 'Participant', 'Участник', 'Участника', 'Участники', [
        'FIELDS' => [
            'ACTIVE' => ['DEFAULT_VALUE' => 'N'],
            'CODE' => ['DEFAULT_VALUE' => ['UNIQUE' => 'Y'], 'IS_REQUIRED' => 'Y'],
            'NAME' => ['DEFAULT_VALUE' => '(заполняется автоматически)'],
        ],
    ]);

    $p->createPropertyElement('event', 'Participant', 'EVENT', 'Событие', 'content', 'Event', true);

    $p->createPropertyUser('event', 'Participant', 'USER', 'Пользователь', true);

    $p->createPropertyText('event', 'Participant', 'REJECTION', 'Причина отклонения');

    $i->createIblock('event', 'Template', 'Шаблон', 'Шаблон', 'Шаблоны');

    $p->createPropertyElement('event', 'Template', 'TASK_TYPE', 'Тип задания', 'directory', 'TaskType');

    $p->createPropertyNumber('event', 'Template', 'MAX_DURATION', 'Продолжительность выполнения');

    $p->createPropertyNumber('event', 'Template', 'BONUS', 'Бонус за задание');

    $p->createPropertyEnum('event', 'Template', 'REQUIRED', 'Обязательность');

    $e->createIblockPropertyEnum('event', 'Template', 'REQUIRED', 'Y', 'Обязательное');

    $e->createIblockPropertyEnum('event', 'Template', 'REQUIRED', 'N', 'Необязательное');

    $i->createIblock('event', 'Task', 'Задание', 'Задание', 'Задания');

    $p->createPropertyElement('event', 'Task', 'EVENT', 'Событие', 'content', 'Event', true);

    $p->createPropertyElement('event', 'Task', 'TASK_TYPE', 'Тип задания', 'directory', 'TaskType', true);

    $p->createPropertyNumber('event', 'Task', 'MAX_DURATION', 'Продолжительность выполнения');

    $p->createPropertyNumber('event', 'Task', 'BONUS', 'Бонус за задание', false, ['DEFAULT_VALUE' => '1']);

    $p->createPropertyBool('event', 'Task', 'REQUIRED', 'Обязательное');

    $p->createPropertyDate('event', 'Task', 'DUE', 'Срок выполнения');

    $p->createPropertyWebForm('event', 'Task', 'FORM', 'Форма для задания «заполнить форму»');

    $p->createPropertyNumber('event', 'Task', 'LATITUDE', 'Широта для задания «посетить место»');

    $p->createPropertyNumber('event', 'Task', 'LONGITUDE', 'Долгота для задания «посетить место»');

    $p->createPropertyNumber('event', 'Task', 'MAX_FILES', 'Максимальное количество файлов для задания «загрузи файл»');

    $p->createPropertyString('event', 'Task', 'FILE_TYPES', 'Допустимые расширения файлов для задания «загрузи файл»');

    $p->createPropertyString('event', 'Task', 'VIDEO', 'Видеоролик для задания «посмотри видеоролик»');

    $i->createIblock('event', 'Completion', 'Выполнение', 'Выполнение', 'Выполнения', [
        'FIELDS' => [
            'ACTIVE' => ['DEFAULT_VALUE' => 'N'],
            'CODE' => ['DEFAULT_VALUE' => ['UNIQUE' => 'Y'], 'IS_REQUIRED' => 'Y'],
            'NAME' => ['DEFAULT_VALUE' => '(заполняется автоматически)'],
        ],
    ]);

    $p->createPropertyElement('event', 'Completion', 'TASK', 'Задание', 'event', 'Task', true);

    $p->createPropertyElement('event', 'Completion', 'PARTICIPANT', 'Участник', 'event', 'Participant', true);

    $p->createPropertyWebFormResult('event', 'Completion', 'ANSWER', 'Ответы для задания «заполнить форму»');

    $p->createPropertyFile('event', 'Completion', 'ANSWER', 'Файлы для задания «загрузи файл»', false, [
        'MULTIPLE' => 'Y',
    ]);
};
