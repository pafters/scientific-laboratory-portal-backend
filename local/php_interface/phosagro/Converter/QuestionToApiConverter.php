<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Manager\EventManager;
use Phosagro\Manager\FileManager;
use Phosagro\Manager\QuestionTopicManager;
use Phosagro\Object\Event;
use Phosagro\Object\Question;
use Phosagro\Object\QuestionTopic;
use Phosagro\System\UrlManager;
use Phosagro\Util\Collection;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class QuestionToApiConverter
{
    public function __construct(
        private readonly EventManager $events,
        private readonly QuestionTopicManager $questionTopics,
        private readonly FileManager $files,
        private readonly UrlManager $urls,
    ) {
    }

    /**
     * @param Question|Question[] $questionList
     *
     * @return \WeakMap<Question,array>
     */
    public function convertQuestionsToApi(array|Question $questionList): \WeakMap
    {
        /** @var \WeakMap<Question,array> $result */
        $result = new \WeakMap();

        if ($questionList instanceof Question) {
            $questionList = [$questionList];
        }

        $eventIndex = $this->convertEventList($questionList);
        $fileIndex = $this->convertFileList($questionList);
        $topicIndex = $this->convertTopicList($questionList);

        foreach ($questionList as $question) {
            $item = [
                'createdAt' => Date::toFormat($question->questionCreatedAt, DateFormat::BITRIX),
                'id' => sprintf('%d', $question->questionIdentifier),
                'question' => $question->questionQuestion,
                'topic' => $topicIndex[$question],
                'type' => $question->questionType->name,
            ];

            $event = $eventIndex[$question] ?? null;

            if (null !== $event) {
                $item['event'] = $event;
            }

            $file = $fileIndex[$question] ?? null;

            if (null !== $file) {
                $item['file'] = $file;
            }

            if ('' !== $question->questionResponse) {
                $item['response'] = $question->questionResponse;
            }

            if ('' !== $question->questionUrl) {
                $item['url'] = $question->questionUrl;
            }

            ksort($item, SORT_STRING);

            $result[$question] = $item;
        }

        return $result;
    }

    private function convertEvent(Event $event): array
    {
        return [
            'id' => sprintf('%d', $event->id),
            'name' => $event->name,
        ];
    }

    /**
     * @param Question[] $questionList
     *
     * @return \WeakMap<Question,array>
     */
    private function convertEventList(array $questionList): \WeakMap
    {
        /** @var \WeakMap<Question,array> $result */
        $result = new \WeakMap();

        $eventIdentifierList = Collection::identifierList(
            array_map(
                static fn(Question $question): ?int => $question->questionEventIdentifier,
                $questionList,
            )
        );

        $eventIndex = $this->events->getEventsByIdentifiers($eventIdentifierList);

        foreach ($questionList as $question) {
            if (null !== $question->questionEventIdentifier) {
                $event = $eventIndex[$question->questionEventIdentifier] ?? null;
                if (null !== $event) {
                    $result[$question] = $this->convertEvent($event);
                }
            }
        }

        return $result;
    }

    /**
     * @param Question[] $questionList
     *
     * @return \WeakMap<Question,string>
     */
    private function convertFileList(array $questionList): \WeakMap
    {
        /** @var \WeakMap<Question,string> $result */
        $result = new \WeakMap();

        foreach ($questionList as $question) {
            if (null !== $question->questionFileIdentifier) {
                $path = \CFile::GetPath($question->questionFileIdentifier);
                $path = \is_string($path) ? trim($path) : '';
                if ('' !== $path) {
                    $file = $this->files->getFileByIdentifier($question->questionFileIdentifier);
                    $result[$question] = [
                        'url' => $this->urls->makeAbsolute($path),
                        'file' => $file[0]
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @param Question[] $questionList
     *
     * @return \WeakMap<Question,array>
     */
    private function convertTopicList(array $questionList): \WeakMap
    {
        /** @var \WeakMap<Question,array> $result */
        $result = new \WeakMap();

        foreach ($questionList as $question) {
            $topic = $this->questionTopics->findOne($question->questionTopicIdentifier) ?? new QuestionTopic(0, '');
            $result[$question] = $topic->toApi();
        }

        return $result;
    }
}
