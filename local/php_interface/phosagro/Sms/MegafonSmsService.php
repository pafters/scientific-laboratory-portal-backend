<?php

declare(strict_types=1);

namespace Phosagro\Sms;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\Error;
use Bitrix\Main\Web\HttpClient;
use Bitrix\MessageService\MessageStatus;
use Bitrix\MessageService\Sender\Base;
use Bitrix\MessageService\Sender\Result\SendMessage;
use Bitrix\MessageService\Sender\Util;
use Phosagro\Enum\LogEvent;
use Phosagro\Log\Logger;
use Phosagro\ServiceContainer;
use Phosagro\Sms\MegafonSmsServiceException\AbstractMegafonException;
use Phosagro\Sms\MegafonSmsServiceException\WrongCodeException;
use Phosagro\Sms\MegafonSmsServiceException\WrongFieldsException;
use Phosagro\Sms\MegafonSmsServiceException\WrongFormatException;
use Phosagro\System\Array\Accessor;
use Phosagro\System\Array\AccessorException;
use Phosagro\System\UrlManager;
use Phosagro\Util\Json;
use Phosagro\Util\Text;

/**
 * SMS-сервис Мегафон.
 */
final class MegafonSmsService extends Base
{
    public const IDENTIFIER = 'megafon';

    public function canUse(): bool
    {
        return true;
    }

    public function getFromList(): array
    {
        return [
            [
                'id' => '+00000000000',
                'name' => 'По умолчанию',
            ],
        ];
    }

    public function getId(): string
    {
        return self::IDENTIFIER;
    }

    public function getName(): string
    {
        return 'Мегафон';
    }

    public function getShortName(): string
    {
        return 'Мегафон';
    }

    public static function resolveStatus($serviceStatus)
    {
        return match ($serviceStatus) {
            'delivered' => MessageStatus::DELIVERED,
            'delivery_failed' => MessageStatus::UNDELIVERED,
            default => MessageStatus::UNKNOWN
        };
    }

    public function sendMessage(array $fields): SendMessage
    {
        $result = new SendMessage();

        if (!$this->canUse()) {
            $result->setStatus(MessageStatus::EXCEPTION);
            $result->addError(new Error(GetMessage('MEGAFON_CAN_NOT_BE_USED')));

            return $result;
        }

        $apiPassword = Configuration::getValue('megafon_api_password');
        $apiPassword = (\is_string($apiPassword) ? trim($apiPassword) : '');
        $apiUrl = Configuration::getValue('megafon_api_url');
        $apiUrl = (\is_string($apiUrl) ? trim($apiUrl) : '');
        $apiUser = Configuration::getValue('megafon_api_user');
        $apiUser = (\is_string($apiUser) ? trim($apiUser) : '');

        if (('' === $apiPassword) || ('' === $apiUrl) || ('' === $apiUser)) {
            $result->setStatus(MessageStatus::EXCEPTION);
            $result->addError(new Error(GetMessage('MEGAFON_NOT_CONFIGURED')));

            return $result;
        }

        $requestData = [
            'callback_url' => self::getUrlManager()->makeAbsolute('/api/megafon/update-sms-status/'),
            'from' => (string) ($fields['MESSAGE_FROM'] ?? ''),
            'message' => $this->prepareMessageBodyForSend($fields['MESSAGE_BODY']),
            'to' => (string) ($fields['MESSAGE_TO'] ?? ''),
        ];

        $requestHeaders = [
            'Authorization' => 'Basic '.base64_encode("{$apiUser}:{$apiPassword}"),
            'Content-Type' => 'application/json',
            'User-Agent' => 'Phosagro',
        ];

        $requestBody = Json::encode($requestData);

        $requestMethod = HttpClient::HTTP_POST;

        $requestUrl = "{$apiUrl}/sms/v1/sms";

        $httpClient = new HttpClient([
            'charset' => 'UTF-8',
            'headers' => $requestHeaders,
            'socketTimeout' => $this->socketTimeout,
            'streamTimeout' => $this->streamTimeout,
            'waitResponse' => true,
        ]);

        $isSent = $httpClient->query($requestMethod, $requestUrl, $requestBody);

        $httpResponseStatus = $httpClient->getStatus();
        $httpResponseBody = $httpClient->getResult();

        if ($isSent) {
            if (200 === $httpResponseStatus) {
                try {
                    $result->setExternalId($this->parseMegafonResponse($httpResponseBody));
                    $result->setAccepted();
                } catch (WrongCodeException $megafonError) {
                    $result->setStatus(MessageStatus::ERROR);
                    $result->addError(new Error($megafonError->getMessage()));
                } catch (AbstractMegafonException $httpError) {
                    $result->setStatus(MessageStatus::EXCEPTION);
                    $result->addError(new Error($httpError->getMessage()));
                }
            } else {
                $result->setStatus(MessageStatus::EXCEPTION);
                $result->addError(new Error(GetMessage('MEGAFON_RESPONSE_FAILED', [
                    '#ERROR#' => sprintf('[%d] %s', $httpResponseStatus, Text::brief($httpResponseBody)),
                ])));
            }
        } else {
            $result->setStatus(MessageStatus::EXCEPTION);
            $result->addError(new Error(GetMessage('MEGAFON_REQUEST_FAILED', [
                '#ERROR#' => Util::getHttpClientErrorString($httpClient),
            ])));
        }

        $requestHeadersString = $httpClient->getRequestHeaders()->toString();
        $responseHeadersString = $httpClient->getHeaders()->toString();

        if ($result->isSuccess()) {
            $logEvent = LogEvent::MEGAFON_REQUEST_SUCCESS;
            $logError = '';
        } else {
            $logEvent = LogEvent::MEGAFON_REQUEST_FAIL;
            $logError = implode("\n", $result->getErrorMessages());
        }

        self::getLogger()->log(
            $logEvent,
            '',
            <<<HTTP
            {$requestMethod} {$requestUrl}
            {$requestHeadersString}

            {$requestBody}
            <<< RESPONSE {$httpResponseStatus} >>>
            {$responseHeadersString}

            {$httpResponseBody}
            <<< ERRORS >>>
            {$logError}
            HTTP
        );

        return $result;
    }

    private static function getLogger(): Logger
    {
        return ServiceContainer::getInstance()->get(Logger::class);
    }

    private static function getUrlManager(): UrlManager
    {
        return ServiceContainer::getInstance()->get(UrlManager::class);
    }

    private function parseMegafonResponse(string $body): string
    {
        try {
            $data = Json::decode($body);
        } catch (\Throwable) {
            throw new WrongFormatException('NOT_A_JSON', $body);
        }

        if (!\is_array($data)) {
            throw new WrongFormatException('NOT_AN_OBJECT', $body);
        }

        $accessor = new Accessor($data);

        try {
            $megafonCode = $accessor->getObject('result')->getObject('status')->getInt('code');

            if (0 !== $megafonCode) {
                $megafonError = $accessor->getObject('result')->getObject('status')->getStringTrimmed('description');

                throw new WrongCodeException($body, $megafonCode, $megafonError);
            }

            return $accessor->getObject('result')->getStringTrimmed('msg_id');
        } catch (AccessorException $error) {
            throw new WrongFieldsException($body, $error);
        }
    }
}
