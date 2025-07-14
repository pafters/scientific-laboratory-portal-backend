<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyFile(
        'event',
        'Task',
        'VIDEO_FILE',
        'Файл видеоролика для задания «посмотри видеоролик»',
        false,
        ['SORT' => 130]
    );

    $properties->updateProperty('event', 'Task', 'CORRECT_ANSWER_LIMIT', [
        'NAME' => 'Количество правильных ответов для засчитывания задания «заполнить форму»',
        'SORT' => 75,
    ]);
};
