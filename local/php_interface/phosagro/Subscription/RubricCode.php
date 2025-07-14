<?php

declare(strict_types=1);

namespace Phosagro\Subscription;

use Phosagro\Util\Text;

/**
 * Список известных рассылок.
 */
enum RubricCode
{
    case EVENTS_ACTIVITY;
    case EVENTS_DAILY;
    case EVENTS_MONTHLY;
    case EVENTS_WEEKLY;
    case NEWS_DAILY;
    case NEWS_MONTHLY;
    case NEWS_WEEKLY;
    case TASKS;
    case VOTINGS;

    public function getApiCode(): string
    {
        return Text::lower($this->name);
    }

    public function getIndexKey(): string
    {
        return '~'.$this->name;
    }

    public function getTranslationKey(): string
    {
        return sprintf('SUBSCRIPTION_%s', $this->name);
    }
}
