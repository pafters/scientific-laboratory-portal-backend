<?php declare(strict_types=1);
use Bitrix\Main\SiteTemplateTable;

return static function (): void {
    $result = SiteTemplateTable::add([
        'CONDITION' => '',
        'SITE_ID' => 's1',
        'SORT' => 10,
        'TEMPLATE' => 'api',
    ]);

    if (!$result->isSuccess()) {
        throw new RuntimeException('Can not add template. ' . implode(' ', $result->getErrorMessages()));
    }
};
