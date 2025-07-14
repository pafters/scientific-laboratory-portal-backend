<?php

declare(strict_types=1);

namespace Phosagro\Site;

use Bitrix\Main\SiteTable;
use Phosagro\System\Array\Accessor;

final class SiteInfo
{
    private string $adminEmail;
    private bool $loaded = false;
    private string $siteName;
    private string $siteUrl;

    public function getAdminEmail(): string
    {
        $this->load();

        return $this->adminEmail;
    }

    public function getSiteName(): string
    {
        $this->load();

        return $this->siteName;
    }

    public function getSiteUrl(): string
    {
        $this->load();

        return $this->siteUrl;
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $found = SiteTable::getList([
            'filter' => [
                '=LID' => 's1',
            ],
            'limit' => 1,
            'order' => [
                'LID' => 'ASC',
            ],
            'select' => [
                'EMAIL',
                'SERVER_NAME',
                'SITE_NAME',
            ],
        ])->fetchRaw();

        if (!$found) {
            throw new \RuntimeException('Not found site "s1".');
        }

        $accessor = new Accessor($found);

        $this->adminEmail = $accessor->getNullableStringTrimmed('EMAIL');
        $this->siteName = $accessor->getNullableStringTrimmed('SITE_NAME');
        $this->siteUrl = $accessor->getNullableStringTrimmed('SERVER_NAME');
    }
}
