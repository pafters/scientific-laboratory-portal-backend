<?php

declare(strict_types=1);

namespace Phosagro\Converter;

use Phosagro\Object\Event;

final class EventForSelectToApiConverter
{
    /**
     * @param Event|Event[] $eventList
     *
     * @return \WeakMap<Event,array>
     */
    public function convertEventForSelectToApi(array|Event $eventList): \WeakMap
    {
        /** @var \WeakMap<Event,array> $result */
        $result = new \WeakMap();

        if ($eventList instanceof Event) {
            $eventList = [$eventList];
        }

        foreach ($eventList as $event) {
            $result[$event] = [
                'id' => sprintf('%d', $event->id),
                'name' => $event->name,
            ];
        }

        return $result;
    }
}
