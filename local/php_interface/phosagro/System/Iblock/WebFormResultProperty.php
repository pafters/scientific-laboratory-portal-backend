<?php

declare(strict_types=1);

namespace Phosagro\System\Iblock;

final class WebFormResultProperty
{
    public function getPropertyFieldHtml(
        array $property,
        array $value,
        array $control,
    ): string {
        $formIdentifier = null;

        $resultIdentifier = filter_var($value['VALUE'] ?? null, FILTER_VALIDATE_INT);
        $resultIdentifier = \is_int($resultIdentifier) ? $resultIdentifier : null;

        $resultRow = null;

        if (null !== $resultIdentifier) {
            $resultRow = \CFormResult::GetByID($resultIdentifier)->Fetch();
            $resultRow = \is_array($resultRow) ? $resultRow : [];
            $formIdentifier = filter_var($resultRow['FORM_ID'] ?? null, FILTER_VALIDATE_INT);
            $formIdentifier = \is_int($formIdentifier) ? $formIdentifier : null;
        }

        return sprintf(
            <<<'HTML'
            <input name="%s" type="text" value="%s">
            %s
            HTML,
            $control['VALUE'] ?? '',
            htmlspecialchars($value['VALUE'] ?? ''),
            ((null !== $formIdentifier) && (null !== $resultIdentifier)) ? sprintf(
                <<<'HTML'
                <span> </span>
                <a href="%s">%s</a>
                HTML,
                '/bitrix/admin/form_result_edit.php?'.http_build_query([
                    'RESULT_ID' => sprintf('%s', $resultIdentifier),
                    'WEB_FORM_ID' => sprintf('%d', $formIdentifier),
                    'lang' => LANG,
                ]),
                GetMessage('FORM_RESULT_LINK'),
            ) : '',
        );
    }
}
