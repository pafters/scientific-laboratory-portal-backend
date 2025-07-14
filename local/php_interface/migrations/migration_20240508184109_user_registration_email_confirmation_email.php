<?php

declare(strict_types=1);

use Bitrix\Main\Mail\Internal\EventMessageTable;

return static function (): void {
    $found = EventMessageTable::getList([
        'filter' => [
            '=EVENT_NAME' => 'NEW_USER_CONFIRM',
        ],
        'select' => [
            'ID',
        ],
    ]);

    $first = $found->fetchRaw();

    if (!$first) {
        throw new RuntimeException('Not found message.');
    }

    if ($found->fetchRaw()) {
        throw new RuntimeException('Found more than one message.');
    }

    $message = <<<'MESSAGE'
    Информационное сообщение сайта #SITE_NAME#
    ------------------------------------------

    Здравствуйте,

    Вы получили это сообщение, так как ваш адрес был использован при регистрации нового пользователя на сервере #SERVER_NAME#.

    Ваш код для подтверждения адреса электронной почты: #CONFIRM_CODE#

    ---------------------------------------------------------------------

    Сообщение сгенерировано автоматически.
    MESSAGE;

    $result = EventMessageTable::update($first['ID'], [
        'MESSAGE' => $message,
    ]);

    if (!$result->isSuccess()) {
        $error = implode(' ', $result->getErrorMessages());

        throw new RuntimeException('Can not update message. '.$result);
    }
};
