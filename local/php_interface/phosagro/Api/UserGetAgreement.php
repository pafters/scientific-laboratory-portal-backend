<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Bitrix\Main\UserConsent\Internals\AgreementTable;
use Phosagro\System\Api\Route;
use Phosagro\System\Array\Accessor;

final class UserGetAgreement
{
    #[Route(pattern: '~^/api/user/get-agreement/$~')]
    public function execute(): array
    {
        $found = AgreementTable::getList([
            'filter' => [
                '=CODE' => 'personal-data-agreement',
            ],
            'limit' => 2,
            'select' => [
                'AGREEMENT_TEXT',
                'IS_AGREEMENT_TEXT_HTML',
            ],
        ]);

        $row = $found->fetchRaw();

        if (!$row) {
            throw new \RuntimeException('Not found personal data agreement.');
        }

        if ($found->fetchRaw()) {
            throw new \RuntimeException('Found more than one personal data agreement.');
        }

        $accessor = new Accessor($row);

        $html = $accessor->getNullableStringTrimmed('AGREEMENT_TEXT');

        if ('N' === $row['IS_AGREEMENT_TEXT_HTML']) {
            $html = htmlspecialchars($html);
        }

        return [
            'html' => $html,
        ];
    }
}
