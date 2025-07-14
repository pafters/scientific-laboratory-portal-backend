<?php

declare(strict_types=1);

use Bitrix\Main\Config\Option;

return static function (): void {
    Option::set('main', 'new_user_phone_auth', 'Y');
    Option::set('main', 'new_user_phone_required', 'Y');
};
