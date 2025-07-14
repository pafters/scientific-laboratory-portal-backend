<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Object\Event;
use Phosagro\System\Clock;
use Phosagro\System\ImageManager;
use Phosagro\Util\Date;
use Phosagro\Util\DateFormat;

final class EventForUser
{
    public function __construct(
        private readonly Clock $clock,
        private readonly ImageManager $images,
    ) {}

    public function buildEventForUser(Event $event, bool $slim = false): array
    {
        if ($slim) {
            return [
                'id' => sprintf('%d', $event->id),
                'name' => $event->name,
            ];
        }

        $result = [
            'id' => sprintf('%d', $event->id),
            'name' => $event->name,
            'startsAt' => Date::toFormat($event->startAt ?? $this->clock->now(), DateFormat::BITRIX),
        ];

        if (null !== $event->endAt) {
            $result['endsAt'] = Date::toFormat($event->endAt, DateFormat::BITRIX);
        }

        if ('' !== $event->shortText) {
            $result['shortText'] = $event->shortText;
        }

        $previewPictureIdentifier = $event->getActualPreviewPictureIdentifier();

        $smallPicture = $this->images->resizeImage($previewPictureIdentifier, 'events', 'small_item');

        if ('' !== $smallPicture) {
            $result['smallPicture'] = $smallPicture;
        }

        ksort($result, SORT_STRING);

        return $result;
    }
}
