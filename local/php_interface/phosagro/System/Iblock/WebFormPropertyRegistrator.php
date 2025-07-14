<?php

declare(strict_types=1);

namespace Phosagro\System\Iblock;

use Bitrix\Main\EventManager;
use Phosagro\System\ListenerInterface;

final class WebFormPropertyRegistrator implements ListenerInterface
{
    public function __construct(
        private readonly WebFormProperty $property,
    ) {}

    public function registerListeners(EventManager $eventManager): void
    {
        $eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', $this->getPropertyDefinition(...));
    }

    private function getPropertyDefinition(): array
    {
        return [
            'DESCRIPTION' => 'Привязка к веб-форме',
            'GetPropertyFieldHtml' => $this->property->getPropertyFieldHtml(...),
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'WebForm',
        ];
    }
}
