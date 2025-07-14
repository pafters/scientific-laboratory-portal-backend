<?php

declare(strict_types=1);

use Phosagro\Migration\DatabaseHelper;
use Phosagro\Migration\IblockHelper;

return static function (DatabaseHelper $database, IblockHelper $iblocks): void {
    $manager = new CIBlockElement();

    $database->assertSuccess($manager->Add([
        'CODE' => 'admin_decision',
        'IBLOCK_ID' => $iblocks->getIblockId('directory', 'AccrualReason'),
        'NAME' => 'Начислено администратором',
        'PROPERTY_OWNER' => '1',
        'SORT' => 10,
    ]), 'accrual reason', 'admin_decision', 'create', $manager->LAST_ERROR);

    $database->assertSuccess($manager->Add([
        'CODE' => 'event_completion',
        'IBLOCK_ID' => $iblocks->getIblockId('directory', 'AccrualReason'),
        'NAME' => 'Выполнено задание',
        'PROPERTY_OWNER' => '1',
        'SORT' => 20,
    ]), 'accrual reason', 'event_completion', 'create', $manager->LAST_ERROR);

    $database->assertSuccess($manager->Add([
        'CODE' => 'task_completion',
        'IBLOCK_ID' => $iblocks->getIblockId('directory', 'AccrualReason'),
        'NAME' => 'Завершено событие',
        'PROPERTY_OWNER' => '1',
        'SORT' => 30,
    ]), 'accrual reason', 'task_completion', 'create', $manager->LAST_ERROR);
};
