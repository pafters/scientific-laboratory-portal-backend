<?php

declare(strict_types=1);

use Phosagro\Migration\IblockPropertyHelper;

return static function (IblockPropertyHelper $properties): void {
    $properties->createPropertyNumber('directory', 'AgeCategory', 'MINIMAL_AGE', 'Возраст от (лет, включительно)');
    $properties->createPropertyNumber('directory', 'AgeCategory', 'MAXIMAL_AGE', 'Возраст до (лет, включительно)');
};
