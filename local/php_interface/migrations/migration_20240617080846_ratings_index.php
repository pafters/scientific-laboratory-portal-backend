<?php

declare(strict_types=1);

use Bitrix\Main\DB\Connection;

return static function (Connection $database): void {
    $database->queryExecute(
        <<<'SQL'
        create unique index unique_index
        on phosagro_rating (
            UF_TYPE,
            UF_EVENT,
            UF_PERIOD,
            UF_USER
        );
        SQL,
    );
};
