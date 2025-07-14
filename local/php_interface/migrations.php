<?php

declare(strict_types=1);

namespace Picom;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Result;
use CAdminList;
use Phosagro\ServiceContainer;
use RuntimeException;

use function array_keys;
use function date;
use function filter_input;
use function glob;
use function htmlspecialchars as html;
use function implode;
use function LocalRedirect;

use const FILTER_VALIDATE_BOOLEAN;
use const INPUT_POST;

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

global $APPLICATION, $USER;

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm('');
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('TITLE'));

$checkResult = static function (Result $result): void {
    if (!$result->isSuccess()) {
        throw new RuntimeException(implode(' ', $result->getErrorMessages()));
    }
};

/**
 * @return ?DataManager
 */
$getHighloadBlock = static function () {
    static $table = null;
    if ((null === $table) && Loader::includeModule('highloadblock')) {
        $tableData = HighloadBlockTable::getRow(['filter' => ['=NAME' => 'Migrations']]);
        if (null !== $tableData) {
            HighloadBlockTable::compileEntity($tableData);

            /** @var DataManager $table */
            $table = "{$tableData['NAME']}Table";
        }
    }

    return $table;
};

$getMigrationsHighloadBlockUrl = static function (): ?string {
    $row = HighloadBlockTable::getRow(['filter' => ['=NAME' => 'Migrations'], 'select' => ['ID']]);

    if (null === $row) {
        return null;
    }

    $query = http_build_query([
        'ENTITY_ID' => $row['ID'],
        'lang' => LANG,
    ]);

    return "/bitrix/admin/highloadblock_rows_list.php?{$query}";
};

$findNew = static function () use ($getHighloadBlock): array {
    $result = [];

    $executed = [];

    $table = $getHighloadBlock();
    if (null !== $table) {
        $found = $table::getList(['order' => ['UF_NAME' => 'ASC']]);
        while ($row = $found->fetchRaw()) {
            $executed[$row['UF_NAME']] = true;
        }
    }

    foreach (glob($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/migrations/*.php') as $file) {
        $name = pathinfo($file, PATHINFO_BASENAME);
        if (\array_key_exists($name, $executed)) {
            continue;
        }
        $instance = require $file;
        if (!\is_callable($instance)) {
            throw new RuntimeException("Not a migration {$file}");
        }
        $result[$name] = $instance;
    }

    return $result;
};

$executeNew = static function (
    ServiceContainer $serviceContainer,
) use (
    $findNew,
    $getHighloadBlock,
): void {
    foreach ($findNew() as $name => $migration) {
        $serviceContainer->invoke($migration);
        $table = $getHighloadBlock();
        if (null === $table) {
            throw new RuntimeException('Not found migrations table');
        }
        $addResult = $table::add(['UF_NAME' => $name]);
        if (!$addResult->isSuccess()) {
            throw new RuntimeException(implode(' ', $addResult->getErrorMessages()));
        }
    }
};

if (filter_input(INPUT_POST, 'run', FILTER_VALIDATE_BOOLEAN)) {
    $executeNew(ServiceContainer::getInstance());
    LocalRedirect($APPLICATION->GetCurPageParam());
}

$list = new CAdminList('migrations_table');

$list->AddHeaders([
    ['id' => 'NAME', 'content' => Loc::getMessage('NAME'), 'default' => true],
]);

foreach (array_keys($findNew()) as $name) {
    $row = $list->AddRow($name, [
        'NAME' => $name,
    ]);
}

$getMessage = static function (string $code): string {
    return Loc::getMessage($code);
};

if ([] !== $list->aRows) {
    $list->AddAdminContextMenu([
        [
            'HTML' => <<<END
                <form action="{$APPLICATION->GetCurPageParam()}" method="post">
                    <input name="run" type="hidden" value="1">
                    <button class="adm-btn adm-btn-save adm-btn-add" style="padding-top: 5px" type="submit">
                        {$getMessage('EXECUTE')}
                    </button>
                </form>
            END,
        ],
    ]);
}

$list->CheckListMode();

Asset::getInstance()->addString(
    <<<'END'
    <style>
    .adm-detail-toolbar {
        display: flex;
        flex-flow: row wrap;
        justify-content: space-between;
        padding: 8px 8px 7px 8px;
    }
    .adm-detail-toolbar-btn {
        display: flex;
        flex-flow: row;
        gap: 0;
    }
    .new-migration {
        align-items: baseline;
        display: flex;
        flex-flow: row nowrap;
        gap: 8px;
    }
    </style>
    END,
);

$listUrl = $getMigrationsHighloadBlockUrl();

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php';
?>
    <div>
        <h4>Создание миграции</h4>
        <ol>
            <li>Вводим название в поле Новая.</li>
            <li>Копируем имя файла.</li>
            <li>Создаём файл в папке local/php_interface/migrations.</li>
            <li>
                <span>Содержимое файла:</span>
                <input
                    id="newPhp"
                    readonly
                    size="70"
                    value="&lt;?php declare(strict_types=1); return static function (): void {};"
                >
                <span>← нажмите для копирования</span>
            </li>
            <li>Форматируем файл по стандартам оформления кода.</li>
            <li>Внутри функции пишем миграцию используя API Битрикса.</li>
            <li>
                <a href="https://dev.1c-bitrix.ru/api_help/index.php">
                    <span>Старое API Битрикса</span>
                </a>
                <span>(инфоблоки, пользовательские поля, веб-формы).</span>
            </li>
            <li>
                <a href="https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=5753">
                    <span>Общие принципы нового API Битрикса</span>
                </a>
                <span>(используем если старое API объявлено устаревшим).</span>
            </li>
            <li>
                <a href="https://dev.1c-bitrix.ru/api_d7/">
                    <span>Новое API Битрикса</span>
                </a>
                <span>(нет описания полей, поля смотреть в коде классов *Table).</span>
            </li>
        </ol>
    </div>
    <div>
        <h4>Откат миграции</h4>
        <ol>
            <li>Удаляем миграцию из списка выполненных.</li>
            <li>Через админку вручную отменяем изменения.</li>
        </ol>
    </div>
    <div class="adm-detail-toolbar">
        <?php if (null !== $listUrl) { ?>
            <a
                class="adm-detail-toolbar-btn"
                href="<?php echo html($listUrl); ?>"
                title="<?php echo html(Loc::getMessage('LIST_TITLE')); ?>"
            >
                <span class="adm-detail-toolbar-btn-l"></span>
                <span class="adm-detail-toolbar-btn-text"><?php echo html(Loc::getMessage('LIST')); ?></span>
                <span class="adm-detail-toolbar-btn-r"></span>
            </a>
        <?php } ?>
        <div class="new-migration">
            <div class="new-migration-item">
                <label for="newName"><?php echo html(Loc::getMessage('NEW')); ?>:</label>
                <input id="newName" type="text">
            </div>
            <div class="new-migration-item">
                <label for="newFile"><?php echo html(Loc::getMessage('FILE')); ?>:</label>
                <input id="newFile" readonly type="text">
            </div>
            <div class="new-migration-item">
                <input id="newDate" type="hidden" value="<?php echo html(date('YmdHis')); ?>">
                <input id="copyName" type="button" value="<?php echo html(Loc::getMessage('COPY')); ?>" onclick="">
            </div>
        </div>
    </div>
    <?php
$list->DisplayList();
?>
    <script>
        (function () {
            function generate() {
                const date = document.getElementById('newDate').value;
                const name = document.getElementById('newName').value;
                const suffix = name.split(/\s+/g).join('_').toLowerCase().replace(/^_+|_+$/g, '');
                const file = suffix ? ('migration_' + date + '_' + suffix + '.php') : '';
                document.getElementById('newFile').value = file;
                document.getElementById('copyName').disabled = !file;
            }

            generate();

            document.getElementById('newName').addEventListener('change', function () {
                generate();
            });

            document.getElementById('newName').addEventListener('keyup', function () {
                generate();
            });

            document.getElementById('newName').addEventListener('paste', function () {
                generate();
            });

            document.getElementById('copyName').addEventListener('click', function (event) {
                event.preventDefault();
                const copyText = document.getElementById('newFile');
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
            });

            document.getElementById('newPhp').addEventListener('click', function (event) {
                event.preventDefault();
                const copyText = document.getElementById('newPhp');
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value);
            });
        })();
    </script>
    <?php

require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';
