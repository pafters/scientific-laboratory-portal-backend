<?php

declare(strict_types=1);

use Bitrix\Main\Config\Option;

return static function (): void {
    Option::set('main', 'event_log_block_user', 'Y');
    Option::set('main', 'event_log_register', 'Y');
    Option::set('main', 'event_log_register_fail', 'Y');
};
