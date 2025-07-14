<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Manager\CityManager;
use Phosagro\Manager\DbManager;
use Phosagro\System\ImageManager;
use Phosagro\System\UrlManager;

final class FeedsManager
{
    function __construct(
        private CityManager $cityManager,
        private DbManager $dbManager,
        private readonly UrlManager $urlManager,
        private readonly ImageManager $imageManager
    ) {
    }

    public function getAll(string $page, string $counts, string|null $city): array
    {
        $feeds = [];
        if (filter_var($page, FILTER_VALIDATE_INT) && filter_var($counts, FILTER_VALIDATE_INT)) {

            $data = $this->dbManager->getAllFeeds($counts, $page, $city);

            $i = 0;
            while ($feed = $data->Fetch()) {
                //var_export([$i, $page, $feed]);
                $pictureId = $feed['PREVIEW_PICTURE'] ?? $feed['DETAIL_PICTURE'];
                $feeds[] = [
                    'id' => $feed['ID'],
                    'name' => $feed['NAME'],
                    'date_start' => $feed['ACTIVE_FROM'],
                    'date_end' => $feed['ACTIVE_TO'],
                    'preview_text' => $feed['PREVIEW_TEXT'],
                    'preview_picture' => ($i === 0 && $page == 1) ? $this->imageManager->resizeImage($pictureId, 'news', 'main')
                        : $this->imageManager->resizeImage($pictureId, 'news', 'small_item'),
                    'important' => !is_null($feed['PROPERTY_IMPORTANT_VALUE'])
                ];
                $i++;
            }
        }
        return ['feeds' => $feeds];
    }

    public function getOneById(string $id): array
    {
        $feed = null;

        $data = $this->dbManager->getFeedById($id);

        if ($data) {
            $feed = [
                'id' => $data['ID'],
                'name' => $data['NAME'],
                'date_start' => $data['ACTIVE_FROM'],
                'date_end' => $data['ACTIVE_TO'],
                'detail_picture' => $this->imageManager->resizeImage($data['DETAIL_PICTURE'], 'detail'),
                'detail_text' => $data['DETAIL_TEXT'],
                'photos' => $this->getPhotos($data['PROPERTY_PHOTOS_VALUE']),
            ];

            if (null !== $data['PROPERTY_CITY_VALUE']) {
                $feed['city'] = $this->getCity($data['PROPERTY_CITY_VALUE']);
            }

            ksort($feed, SORT_STRING);
        }
        if (!$feed)
            \CHTTP::SetStatus(404);
        return ['feed' => $feed];
    }

    private function getCity(string $id): string
    {
        $city = $this->cityManager->findOne(intval($id));
        return $city->code;
    }

    private function getPhotos(array $photos): array
    {
        $photo_paths = [];
        foreach ($photos as $elementId) {
            $resizeImage = $this->imageManager->resizeImage($elementId, 'gallery', 'small_item');
            $clearImage = $this->imageManager->resizeImage($elementId, 'none');
            $photo_paths[] = [
                'small' => $resizeImage,
                'big' => $clearImage
            ];
        }

        return $photo_paths;
    }
}
