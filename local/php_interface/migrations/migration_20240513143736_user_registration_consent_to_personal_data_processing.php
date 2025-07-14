<?php

declare(strict_types=1);

use Bitrix\Main\Config\Option;
use Bitrix\Main\UserConsent\Agreement;
use Bitrix\Main\UserConsent\Internals\AgreementTable;

return static function (): void {
    $result = AgreementTable::add([
        'ACTIVE' => 'Y',
        'AGREEMENT_TEXT' => '',
        'CODE' => 'personal-data-agreement',
        'DATA_PROVIDER' => '',
        'IS_AGREEMENT_TEXT_HTML' => 'N',
        'LABEL_TEXT' => 'Принимаю соглашение об обработке персональных данных',
        'LANGUAGE_ID' => '',
        'NAME' => 'Согласие на обработку персональных данных',
        'TYPE' => Agreement::TYPE_STANDARD,
        'URL' => '',
        'USE_URL' => 'N',
    ]);

    if (!$result->isSuccess()) {
        $error = implode(' ', $result->getErrorMessages());

        throw new RuntimeException('Can not add agreement. '.$error);
    }

    Option::set('main', 'new_user_agreement', $result->getId());
};
