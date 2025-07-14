<?php

declare(strict_types=1);

namespace Phosagro\Subscription;

/**
 * Подписки одного пользователя.
 *
 * Загружаются и сохраняются классом Subscriber.
 */
final class Subscriptions
{
    private array $data = [];

    public function isSubscribed(RubricCode $rubric): bool
    {
        return $this->data[$rubric->getIndexKey()] ?? false;
    }

    public function markSubscribed(RubricCode $rubric): void
    {
        $this->data[$rubric->getIndexKey()] = true;
    }

    public function markUnsubscribed(RubricCode $rubric): void
    {
        unset($this->data[$rubric->getIndexKey()]);
    }

    public function toApi(): array
    {
        $data = [];

        foreach (RubricCode::cases() as $rubric) {
            $data[$rubric->getApiCode()] = $this->isSubscribed($rubric);
        }

        return $data;
    }
}
