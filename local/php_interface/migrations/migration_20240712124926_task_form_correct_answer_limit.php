<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyNumber(
        'event',
        'Task',
        'CORRECT_ANSWER_LIMIT',
        'Количество правильных ответов для засчитывания теста',
    );
};
