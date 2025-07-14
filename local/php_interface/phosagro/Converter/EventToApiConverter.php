<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Event\Participatability\Errors\ParticipatabilityException;
use Phosagro\Event\Participatability\ParticipatabilityChecker;
use Phosagro\Manager\PartnerManager;
use Phosagro\Object\Event;
use Phosagro\Object\Partner;
use Phosagro\System\ImageManager;
use Phosagro\User\AuthorizationContext;

final class EventToApiConverter
{
    public function __construct(
        private readonly AuthorizationContext $authorization,
        private readonly ImageManager $images,
        private readonly ParticipatabilityChecker $participatability,
        private readonly PartnerManager $partners,
    ) {}

    /**
     * @param Event|Event[] $eventList
     *
     * @return \WeakMap<Event,array>
     */
    public function convertEventsToApi(array|Event $eventList): \WeakMap
    {
        /** @var \WeakMap<Event,array> $result */
        $result = new \WeakMap();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        /** @var \WeakMap<Event,bool> $participatability */
        $participatability = new \WeakMap();

        $user = $this->authorization->getNullableAuthorizedUser();

        if (null !== $user) {
            $this->participatability->loadPrticipatability($eventList, [$user]);
            foreach ($eventList as $event) {
                try {
                    $this->participatability->assertParticipatable($event, $user);
                    $participatability[$event] = [
                        'participatable' => true,
                    ];
                } catch (ParticipatabilityException $error) {
                    $participatability[$event] = [
                        'participatable' => false,
                        'reason' => $error->reason->name,
                    ];
                }
            }
        } else {
            foreach ($eventList as $event) {
                $participatability[$event] = [
                    'participatable' => false,
                    'reason' => 'USER_IS_NOT_AUTHORIZED',
                ];
            }
        }

        foreach ($eventList as $event) {
            $previewPictureIdentifier = $event->getActualPreviewPictureIdentifier();

            $firstPartner = null;

            if ([] !== $event->partnerIdentifiers) {
                $firstPartnerKey = array_key_first($event->partnerIdentifiers);
                $firstPartner = $this->partners->findOne($event->partnerIdentifiers[$firstPartnerKey]);
            }

            $data = [
                'bigPicture' => $this->images->resizeImage($event->detailPictureIdentifier, 'detail'),
                'fullText' => $event->fullText,
                'gallery' => array_map(fn (int $pictureIdentifier): array => [
                    'small' => $this->images->resizeImage($pictureIdentifier, 'gallery', 'small_item'),
                    'big' => $this->images->resizeImage($pictureIdentifier, 'none'),
                ], $event->galleryPictureIdentifiers),
                'id' => sprintf('%d', $event->id),
                'name' => $event->name,
                'participatability' => $participatability[$event],
                'partners' => array_map(
                    static fn (Partner $partner): array => $partner->toApi(),
                    array_values(
                        array_filter(
                            array_map($this->partners->findOne(...), $event->partnerIdentifiers),
                            '\is_object',
                        ),
                    ),
                ),
                'points' => $event->points,
                'shortText' => $event->shortText,
                'smallPicture' => [
                    'small' => $this->images->resizeImage($previewPictureIdentifier, 'events', 'small_item'),
                    'big' => $this->images->resizeImage($previewPictureIdentifier, 'events', 'big_item'),
                ],
            ];

            if (null !== $event->ageCategory) {
                $data['age'] = $event->ageCategory->name;
            }

            if (null !== $firstPartner) {
                $data['partner'] = $firstPartner->toApi();
            }

            if (null !== $event->startAt) {
                $data['startsAt'] = $event->startAt->format('d.m.Y H:i:s');
            }

            if (null !== $event->endAt) {
                $data['endsAt'] = $event->endAt->format('d.m.Y H:i:s');
            }

            if (null !== $event->city) {
                $data['city'] = $event->city->toApi();
            }

            if ('' !== $event->for) {
                $data['for'] = $event->for;
            }

            ksort($data, SORT_STRING);

            $result[$event] = $data;
        }

        return $result;
    }
}
