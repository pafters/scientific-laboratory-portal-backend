<?php

declare(strict_types=1);

namespace Phosagro\Sms;

use Bitrix\MessageService\Sender\Base;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Phosagro\Util\File;
use Phosagro\Util\Json;

/**
 * Пустой SMS-сервис для тестирования регистрации пользователей.
 *
 * Записывает отправленные sms в файл /sms.txt
 */
final class TestSmsService extends Base
{
    public function canUse(): bool
    {
        return true;
    }

    public function getFromList(): array
    {
        return [
            [
                'id' => '+00000000000',
                'name' => 'По умолчанию',
            ],
        ];
    }

    public function getId(): string
    {
        return 'test';
    }

    public function getName(): string
    {
        return 'Тестовый обработчик (файл /sms.txt)';
    }

    public function getShortName(): string
    {
        return 'Тест';
    }

    public function sendMessage(array $fields): SendMessage
    {
        File::write($_SERVER['DOCUMENT_ROOT'].'/sms.txt', date('c').' '.Json::encode($fields), "\n");

        return new SendMessage();
    }
}
