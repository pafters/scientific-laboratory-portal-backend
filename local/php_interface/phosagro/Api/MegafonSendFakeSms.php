<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;
use Phosagro\ServiceContainer;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\HasCustomResponseData;
use Phosagro\System\Api\Route;
use Phosagro\System\Clock;
use Phosagro\Util\File;

final class MegafonSendFakeSms
{
    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly Clock $clock,
    ) {}

    #[HasCustomResponseData]
    #[Route(method: 'POST', pattern: '~^/api/megafon/sms/v1/sms$~')]
    public function execute(): array
    {
        $method = '';
        $headers = '';
        $uri = '';

        $request = Application::getInstance()->getContext()->getRequest();

        if ($request instanceof HttpRequest) {
            $headers = $request->getHeaders()->toString();
            $method = $request->getRequestMethod() ?? '';
            $uri = $request->getRequestUri() ?? '';
        }

        File::write(
            $_SERVER['DOCUMENT_ROOT'].\DIRECTORY_SEPARATOR.'sms.txt',
            date('c')." {$method} {$uri}\n{$headers}\n".File::readInput(),
            "\n",
        );

        $now = $this->clock->now();
        $state = (((int) $now->format('i')) % 4);

        if (0 === $state) {
            return [
                'result' => [
                    'status' => [
                        'code' => -1,
                        'description' => GetMessage('MEGAFON_FAKE_ERROR'),
                    ],
                ],
            ];
        }

        $identifier = sprintf('~%d', $now->getTimestamp());

        $updateStatusParameters = [
            'msg_id' => $identifier,
            'status' => match ($state) {
                1 => 'delivered',
                2 => 'delivery_failed',
                default => 'wrong_status',
            },
        ];

        \CAgent::AddAgent(sprintf(
            '%s::getInstance()->get(%s::class)->executeAgent(%s::getInstance()->get(%s::class)->createFromArray(%s));',
            ServiceContainer::class,
            MegafonUpdateSmsStatus::class,
            ServiceContainer::class,
            AccessorFactory::class,
            var_export($updateStatusParameters, true),
        ), interval: 30);

        return [
            'result' => [
                'msg_id' => $identifier,
                'status' => [
                    'code' => 0,
                    'description' => 'ok',
                ],
            ],
        ];
    }
}
