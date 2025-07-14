<?php

declare(strict_types=1);

return static function (): void {
    $groupManager = new CGroup();

    $result = $groupManager->Update(2, [
        'SECURITY_POLICY' => serialize([
            'BLOCK_LOGIN_ATTEMPTS' => 5,
            'BLOCK_TIME' => 1440,
        ]),
    ]);

    if (!$result) {
        throw new RuntimeException('Can not update group.');
    }
};
