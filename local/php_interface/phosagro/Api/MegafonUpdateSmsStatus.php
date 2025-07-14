<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Bitrix\MessageService\Message;
use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\Sms\MegafonSmsService;
use Phosagro\System\Api\Accessor;
use Phosagro\System\Api\BindRequestBody;
use Phosagro\System\Api\Errors\BadRequestError;
use Phosagro\System\Api\HasCustomResponseData;
use Phosagro\System\Api\Route;
use Phosagro\Util\Json;

final class MegafonUpdateSmsStatus
{
    private const MSG_ID = 'msg_id';
    private const RECEIPTED_MESSAGE_ID = 'receipted_message_id';
    private const SHORT_MESSAGE = 'short_message';
    private const STATUS = 'status';

    public function __construct(
        private readonly Logger $logger,
    ) {}

    #[HasCustomResponseData]
    #[Route(method: 'POST', pattern: '~^/api/megafon/update\-sms\-status/$~')]
    public function execute(#[BindRequestBody] Accessor $input): array
    {
        $input->assertStringFilled(self::MSG_ID);
        $input->assertNullableStringTrimmed(self::RECEIPTED_MESSAGE_ID);
        $input->assertNullableStringTrimmed(self::SHORT_MESSAGE);
        $input->assertStringFilled(self::STATUS);

        try {
            $input->checkErrors();
        } catch (BadRequestError) {
            $this->logger->log(
                LogEvent::MEGAFON_REQUEST_WRONG,
                '',
                Json::encode($input->getData()),
            );

            return [];
        }

        $identifier = $input->getStringFilled(self::MSG_ID);

        $message = Message::loadByExternalId(MegafonSmsService::IDENTIFIER, $identifier);

        if (null === $message) {
            $this->logger->log(
                LogEvent::MEGAFON_SMS_NOT_FOUND,
                $identifier,
                Json::encode($input->getData()),
            );

            return [];
        }

        $status = $input->getStringFilled(self::STATUS);

        $updated = $message->updateStatusByExternalStatus($status);

        if (!$updated) {
            $this->logger->log(
                LogEvent::MEGAFON_STATUS_WRONG,
                $status,
                Json::encode($input->getData()),
            );

            return [];
        }

        $this->logger->log(
            LogEvent::MEGAFON_STATUS_UPDATED,
            "{$identifier} {$status}",
            Json::encode($input->getData()),
        );

        return [];
    }

    public function executeAgent(Accessor $accessor): string
    {
        $this->execute($accessor);

        return '';
    }
}
