<?php

declare(strict_types=1);

namespace Phosagro\Api;

use CDBResult;
use Phosagro\System\Api\Route;

final class SystemFindWebForm
{
    #[Route(pattern: '~^/api/system/find\\-web\\-form/(?<query>[^/]+)/$~')]
    public function execute(string $query): array
    {
        $result = [];

        $by = 's_id';
        $order = 'asc';

        $found = \CForm::GetList($by, $order, ['NAME' => \urldecode($query)]);

        if ($found instanceof CDBResult) {
            while ($row = $found->Fetch()) {
                $result[] = [
                    'id' => \sprintf('%d', $row['ID']),
                    'name' => (string) $row['NAME'],
                ];
            }
        }

        return ['forms' => $result];
    }
}
