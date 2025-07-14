<?php

declare(strict_types=1);

namespace Phosagro\User\Constraints;

use Bitrix\Main\EventManager;
use Phosagro\System\ListenerInterface;

abstract class AbstractUserEditingListener implements ListenerInterface
{
    public function __construct(
        private readonly \CMain $bitrix,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('main', 'OnBeforeUserAdd', function (array &$fields): bool {
            try {
                $this->execute($fields);
            } catch (\Throwable $error) {
                $this->bitrix->ThrowException($error->getMessage());

                return false;
            }

            return true;
        });

        $eventManager->addEventHandler('main', 'OnBeforeUserUpdate', function (array &$fields): bool {
            try {
                $this->execute($fields);
            } catch (\Throwable $error) {
                $this->bitrix->ThrowException($error->getMessage());

                return false;
            }

            return true;
        });
    }

    abstract protected function execute(array &$fields): void;
}
