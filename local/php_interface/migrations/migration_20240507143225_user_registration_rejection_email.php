<?php

declare(strict_types=1);

use Bitrix\Main\Mail\Internal\EventMessageSiteTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use Bitrix\Main\Mail\Internal\EventTypeTable;

return static function (): void {
    $description = <<<'DESCRIPTION'
    #DEFAULT_EMAIL_FROM# - адрес электронной почты сервера
    #EMAIL_TO# - адрес электронной почты пользователя
    #OBJECTION_EMAIL# - адрес электронной почты модератора
    #SERVER_NAME# - название сайта
    DESCRIPTION;

    $typeResult = EventTypeTable::add([
        'DESCRIPTION' => $description,
        'EVENT_NAME' => 'NEW_USER_REJECTION',
        'EVENT_TYPE' => EventTypeTable::TYPE_EMAIL,
        'LID' => 'ru',
        'NAME' => 'Уведомление об отклонении нового пользователя',
        'SORT' => 30,
    ]);

    if (!$typeResult->isSuccess()) {
        $typeError = implode(' ', $typeResult->getErrorMessages());

        throw new RuntimeException('Can not add event type. '.$typeError);
    }

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SERVER_NAME#
    ------------------------------------------

    Мы не смогли вас зарегистрировать, извините.

    Напишите на #OBJECTION_EMAIL# если считаете это ошибкой.

    Письмо сгенерировано автоматически.
    MESSAGE;

    $messageResult = EventMessageTable::add([
        'ACTIVE' => 'Y',
        'BCC' => '',
        'BODY_TYPE' => 'text',
        'CC' => '',
        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO' => '#EMAIL_TO#',
        'EVENT_NAME' => 'NEW_USER_REJECTION',
        'FIELD1_NAME' => '',
        'FIELD1_VALUE' => '',
        'FIELD2_NAME' => '',
        'FIELD2_VALUE' => '',
        'IN_REPLY_TO' => '',
        'LANGUAGE_ID' => '',
        'LID' => 's1',
        'MESSAGE' => $message,
        'MESSAGE_PHP' => '',
        'PRIORITY' => '',
        'REPLY_TO' => '',
        'SITE_TEMPLATE_ID' => '',
        'SUBJECT' => '#SITE_NAME#: Регистрация отклонена',
    ]);

    if (!$messageResult->isSuccess()) {
        $messageError = implode(' ', $messageResult->getErrorMessages());

        throw new RuntimeException('Can not add event message. '.$messageError);
    }

    $messageSiteResult = EventMessageSiteTable::add([
        'EVENT_MESSAGE_ID' => $messageResult->getId(),
        'SITE_ID' => 's1',
    ]);

    if (!$messageSiteResult->isSuccess()) {
        $messageSiteError = implode(' ', $messageSiteResult->getErrorMessages());

        throw new RuntimeException('Can not add event message site. '.$messageSiteError);
    }
};
