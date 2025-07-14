<?php

declare(strict_types=1);

use Bitrix\Main\Config\Option;

return static function (): void {
    Option::set('main', 'event_log_login_fail', 'Y');
    Option::set('main', 'event_log_login_success', 'Y');
    Option::set('main', 'event_log_logout', 'Y');
};
