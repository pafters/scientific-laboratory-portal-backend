<?php

declare(strict_types=1);

use Bitrix\Main\Config\Option;

return static function (): void {
    Option::set('main', 'new_user_registration_email_confirmation', 'Y');
    Option::set('main', 'new_user_email_uniq_check', 'Y');
};
