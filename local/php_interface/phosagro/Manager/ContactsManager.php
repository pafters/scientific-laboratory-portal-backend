<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\System\ImageManager;
use Phosagro\Manager\DbManager;
use Phosagro\Manager\CityManager;

final class ContactsManager
{
    function __construct(
        private DbManager $dbManager,
        private readonly ImageManager $imageManager,
        private readonly CityManager $cityManager,
    ) {
    }
    public function getAll(): array
    {
        $contacts = [];

        $data = $this->dbManager->getAllContacts();

        while ($contact = $data->Fetch()) {
            $contacts[] = [
                'id' => $contact['ID'],
                'site' => $contact['PROPERTY_PROPERTY_SITE_VALUE'],
                'site_name' => $contact['PROPERTY_SITE_NAME_VALUE'],
                'phone' => $contact['PROPERTY_PROPERTY_PHONE_VALUE'],
                'center' => [
                    $contact['PROPERTY_PROPERTY_LATITUDE_VALUE'],
                    $contact['PROPERTY_PROPERTY_LONGITUDE_VALUE'],
                ],
                'schedule' => [
                    'date' => $contact['PROPERTY_PROPERTY_SCHEDULE_VALUE'],
                    'time' => $contact['PROPERTY_PROPERTY_SCHEDULE_DESCRIPTION']
                ],
                'name' => $this->getCityCategory($contact['PROPERTY_CITY_CATEGORY_VALUE']),
                'address' => $contact['PROPERTY_ADDRESS_VALUE'],
                'preview_picture' => $this->imageManager->resizeImage($contact['PREVIEW_PICTURE'], 'contacts', 'small_item'),
            ];
        }

        return ['contacts' => $contacts];
    }

    private function getCityCategory(string|null $id): string|null
    {
        if ($id) {
            $cityCategory = $this->cityManager->findOne(intval($id));
            return $cityCategory->name;
        } else
            return null;
    }
}
