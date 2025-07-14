<?php

declare(strict_types=1);

namespace Phosagro\User\Constraints;

use Bitrix\Main\Localization\Loc;

final class UserEducationalInstitutionNameMustBeFilledIfSelectedType extends AbstractUserEditingListener
{
    public function execute(array &$fields): void
    {
        $eduName = ($fields['UF_EDU_NAME'] ?? '');
        $eduType = ($fields['UF_EDU_TYPE'] ?? '');

        if (('' === $eduName) && ('' !== $eduType)) {
            throw new \DomainException(Loc::getMessage(self::class));
        }
    }
}
