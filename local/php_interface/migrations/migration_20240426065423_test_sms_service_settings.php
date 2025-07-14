<?php

declare(strict_types=1);

use Bitrix\Main\Config\Option;

return static function (): void {
    Option::set('main', 'phone_number_default_country', '1');
    Option::set('main', 'sms_default_sender', '+00000000000');
    Option::set('main', 'sms_default_service', 'test');
};
