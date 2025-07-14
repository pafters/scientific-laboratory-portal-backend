<?php

declare(strict_types=1);

namespace Phosagro;

use Bitrix\Main\Loader;
use Bitrix\MessageService;

final class BitrixAutoloader
{
    public static function register(): void
    {
        $map = [
            'form' => [
                'CForm',
                'CFormAnswer',
                'CFormField',
                'CFormOutput',
                'CFormResult',
                'CFormStatus',
                'CFormValidator',
            ],
            'highloadblock' => [
                'Bitrix\Highloadblock\HighloadBlockLangTable',
                'Bitrix\Highloadblock\HighloadBlockRightsTable',
                'Bitrix\Highloadblock\HighloadBlockTable',
            ],
            'iblock' => [
                'Bitrix\Iblock\ElementPropertyTable',
                'Bitrix\Iblock\ElementTable',
                'Bitrix\Iblock\IblockFieldTable',
                'Bitrix\Iblock\IblockGroupTable',
                'Bitrix\Iblock\IblockMessageTable',
                'Bitrix\Iblock\IblockRssTable',
                'Bitrix\Iblock\IblockSiteTable',
                'Bitrix\Iblock\IblockTable',
                'Bitrix\Iblock\PropertyEnumerationTable',
                'Bitrix\Iblock\PropertyFeatureTable',
                'Bitrix\Iblock\PropertyTable',
                'Bitrix\Iblock\SectionElementTable',
                'Bitrix\Iblock\SectionPropertyTable',
                'Bitrix\Iblock\SectionTable',
                'Bitrix\Iblock\TypeLanguageTable',
                'Bitrix\Iblock\TypeTable',
                'CIBlock',
                'CIBlockElement',
                'CIBlockFormatProperties',
                'CIBlockParameters',
                'CIBlockPriceTools',
                'CIBlockProperty',
                'CIBlockPropertyEnum',
                'CIBlockRSS',
                'CIBlockResult',
                'CIBlockSection',
                'CIBlockType',
                'CIBlockXMLFile',
            ],
            'messageservice' => [
                MessageService\Message::class,
                MessageService\MessageStatus::class,
                MessageService\Sender\Base::class,
                MessageService\Sender\Result\SendMessage::class,
                MessageService\Sender\Util::class,
            ],
            'subscribe' => [
                \CPosting::class,
                \CPostingTemplate::class,
                \CRubric::class,
                \CSubscription::class,
            ],
        ];

        $preparedMap = [];
        foreach ($map as $module => $classes) {
            foreach ($classes as $class) {
                $preparedMap[$class] = $module;
            }
        }

        spl_autoload_register(static function ($classname) use ($preparedMap) {
            if (isset($preparedMap[$classname])) {
                Loader::includeModule($preparedMap[$classname]);
                Loader::autoLoad($classname);
            }
        });
    }
}
