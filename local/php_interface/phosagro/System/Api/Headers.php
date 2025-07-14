<?php

declare(strict_types=1);

namespace Phosagro\System\Api;

use Phosagro\Util\Text;

final class Headers
{
    public static function writeHeaders(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'];
        if ('http://suntsov.phosagro.picom.su' === $origin
            || 'https://suntsov.phosagro.picom.su' === $origin
            || 'https://phosagro.picom.su' === $origin
            || 'https://phosagro.picom.su' === $origin
            || 'http://preprod.phosagro.picom.su/' === $origin
            || 'https://preprod.phosagro.picom.su/' === $origin
            || 'http://localhost:5173' === $origin) {
           header("Access-Control-Allow-Origin: {$origin}");
       }

        header('Access-Control-Allow-Credentials: true');

        if ('OPTIONS' === Text::upper($_SERVER['REQUEST_METHOD'] ?? '')) {
            header('Allow: OPTIONS,GET');
            header('Access-Control-Allow-Methods: OPTIONS,GET');
            header('Access-Control-Allow-Headers: content-type');

            return;
        }

        header('Content-Type: application/json; charset=utf-8');
    }
}
