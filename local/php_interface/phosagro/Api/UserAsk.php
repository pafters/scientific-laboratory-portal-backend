<?php

declare(strict_types=1);

namespace Phosagro\Api;

use Phosagro\Iblocks;
use Phosagro\Manager\Bitrix\UserManager;
use Phosagro\Manager\Errors\NotFoundException;
use Phosagro\Manager\EventManager;
use Phosagro\Manager\FileManager;
use Phosagro\Manager\ParticipantManager;
use Phosagro\Manager\QuestionTopicManager;
use Phosagro\Object\QuestionType;
use Phosagro\System\Api\AccessorFactory;
use Phosagro\System\Api\Errors\NotAuthorizedError;
use Phosagro\System\Api\Errors\ServerError;
use Phosagro\System\Api\Route;
use Phosagro\System\Iblock\Properties;
use Phosagro\System\UrlManager;
use Phosagro\User\AuthorizationContext;
use Phosagro\Util\Text;

final class UserAsk
{
    private const CONTENT = 'content';
    private const EVENT = 'event';
    private const FILE = 'file';
    private const ID = 'id';
    private const NAME = 'name';
    private const QUESTION = 'question';
    private const TOPIC = 'topic';
    private const TYPE = 'type';
    private const URL = 'url';

    public function __construct(
        private readonly AccessorFactory $accessors,
        private readonly AuthorizationContext $authorization,
        private readonly EventManager $events,
        private readonly FileManager $files,
        private readonly ParticipantManager $participants,
        private readonly Properties $properties,
        private readonly QuestionTopicManager $questionTopics,
        private readonly UrlManager $urls,
        private readonly UserManager $users,
    ) {}

    #[Route(method: 'POST', pattern: '~^/api/user/ask/$~')]
    public function execute(): array
    {
        $user = $this->authorization->getNullableAuthorizedUser();

        if (null === $user) {
            throw new NotAuthorizedError();
        }

        $event = null;
        $topic = null;
        $type = null;

        $accessor = $this->accessors->createFromRequest();
        $accessor->assertEnum(self::TYPE, QuestionType::class);
        if (!$accessor->hasFieldError(self::TYPE)) {
            $type = $accessor->getEnum(self::TYPE, QuestionType::class);
        }
        if (QuestionType::EVENT === $type) {
            $accessor->assertObject(self::EVENT);
        } else {
            $accessor->assertOptionalObject(self::EVENT);
        }
        if ($accessor->hasKey(self::EVENT) && !$accessor->hasFieldError(self::EVENT)) {
            $eventAccessor = $accessor->getObject(self::EVENT);
            $eventAccessor->assertStringFilled(self::ID);
            if (!$eventAccessor->hasFieldError(self::ID)) {
                $eventIdentifier = $eventAccessor->getStringFilled(self::ID);
                $eventIdentifier = filter_var($eventIdentifier, FILTER_VALIDATE_INT);
                $event = \is_int($eventIdentifier) ? $this->events->findSingleElement([
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                    'ID' => $eventIdentifier,
                ]) : null;
                if (null === $event) {
                    $accessor->addErrorUnexpected(self::EVENT);
                } else {
                    try {
                        $this->participants->getConfirmedParticipant($event, $user);
                    } catch (NotFoundException) {
                        $accessor->addErrorUnexpected(self::EVENT);
                    }
                }
            }
        }
        $accessor->assertOptionalObject(self::FILE);
        if ($accessor->hasKey(self::FILE) && !$accessor->hasFieldError(self::FILE)) {
            $fileAccessor = $accessor->getObject(self::FILE);
            $fileAccessor->assertBase64Filled(self::CONTENT);
            $fileAccessor->assertStringFilled(self::NAME);
        }
        $accessor->assertStringFilled(self::QUESTION);
        $accessor->assertObject(self::TOPIC);
        if (!$accessor->hasFieldError(self::TOPIC)) {
            $topicAccessor = $accessor->getObject(self::TOPIC);
            $topicAccessor->assertStringFilled(self::ID);
            if (!$topicAccessor->hasFieldError(self::ID)) {
                $topicIdentifier = $topicAccessor->getStringFilled(self::ID);
                $topicIdentifier = filter_var($topicIdentifier, FILTER_VALIDATE_INT);
                $topic = \is_int($topicIdentifier) ? $this->questionTopics->findOne($topicIdentifier) : null;
                if (null === $topic) {
                    $accessor->addErrorUnexpected(self::TOPIC);
                }
            }
        }
        $accessor->assertNullableStringTrimmed(self::URL);
        $accessor->checkErrors();

        if (QuestionType::CONTENT === $type) {
            $moderator = $this->users->findPublicModerator();
        } elseif (QuestionType::EVENT === $type) {
            $moderator = (null === $event) ? null : $this->users->findById($event->moderatorIdentifier);
        } else {
            $moderator = $this->users->findTechnicalAdministrator();
        }

        if (null === $moderator) {
            throw new ServerError([GetMessage('NO_MODERATOR')]);
        }

        if ('' === trim($moderator->email)) {
            throw new ServerError([GetMessage('NO_MODERATOR_EMAIL')]);
        }

        $manager = new \CIBlockElement();

        $fileIdentifier = null;

        if ($accessor->hasKey(self::FILE)) {
            $fileIdentifier = $this->files->saveFile(
                $accessor->getObject(self::FILE)->getBase64Filled(self::CONTENT),
                $accessor->getObject(self::FILE)->getBase64Filled(self::NAME),
                'ask',
            );
        }

        $properties = [
            'EVENT' => $event?->id,
            'FILE' => $fileIdentifier,
            'MODERATOR' => $moderator->userIdentifier,
            'TOPIC' => $topic?->questionTopicIdentifier,
            'TYPE' => $this->properties->getEnumId(Iblocks::questionId(), 'TYPE', $type->name),
            'URL' => $accessor->getNullableStringTrimmed(self::URL),
            'USER' => $user->userIdentifier,
        ];

        $addResult = $manager->Add([
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => Iblocks::questionId(),
            'NAME' => sprintf('%s - %s', $user->login, Text::brief($accessor->getStringFilled(self::QUESTION))),
            'PREVIEW_TEXT' => $accessor->getStringFilled(self::QUESTION),
            'PREVIEW_TEXT_TYPE' => 'text',
            'PROPERTY_VALUES' => $properties,
        ]);

        if (!$addResult) {
            throw new \RuntimeException('Failed to add question. '.$manager->LAST_ERROR);
        }

        $questionIdentifier = (int) $addResult;

        $adminUrl = $this->urls->makeAbsolute(sprintf(
            '/bitrix/admin/iblock_element_edit.php?%s',
            http_build_query([
                'IBLOCK_ID' => sprintf('%d', Iblocks::questionId()),
                'ID' => sprintf('%d', $questionIdentifier),
                'WF' => 'Y',
                'find_section_section' => '-1',
                'lang' => 'ru',
                'type' => 'questions',
            ])
        ));

        $sendResult = \CEvent::Send('QUESTION_ADDED', 's1', [
            'ADMIN_URL' => $adminUrl,
            'MODERATOR_EMAIL' => $moderator->email,
            'USER_LOGIN' => $user->login,
        ]);

        if (!$sendResult) {
            throw new \RuntimeException('Failed to send email QUESTION_ADDED.');
        }

        return [];
    }
}
