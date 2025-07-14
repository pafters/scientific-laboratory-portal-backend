<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\System\ImageManager;
use Phosagro\Manager\DbManager;

final class VideoManager
{
    function __construct(
        private DbManager $dbManager,
        private readonly ImageManager $imageManager,
    ) {
    }
    public function getAll(string $page, string $counts): array
    {
        $videos = [];
        if (filter_var($page, FILTER_VALIDATE_INT) && filter_var($counts, FILTER_VALIDATE_INT)) {

            $data = $this->dbManager->getAllVideos($counts, $page);

            while ($video = $data->Fetch()) {
                $videos[] = [
                    'id' => $video['ID'],
                    'name' => $video['NAME'],
                    'date_start' => $video['ACTIVE_FROM'],
                    'date_end' => $video['ACTIVE_TO'],
                    'preview_picture' => [
                        'big' => $this->imageManager->resizeImage($video['PREVIEW_PICTURE'], 'courses', 'big_item'),
                        'small' => $this->imageManager->resizeImage($video['PREVIEW_PICTURE'], 'courses', 'small_item'),
                    ]
                ];

            }
        }
        return ['videos' => $videos];
    }

    public function getOneById(string $id): array
    {
        $video = null;

        $data = $this->dbManager->getVideoById($id);

        if ($data) {
            $video = [
                'id' => $data['ID'],
                'name' => $data['NAME'],
                'date_start' => $data['ACTIVE_FROM'],
                'date_end' => $data['ACTIVE_TO'],
                'video' => $data['PREVIEW_TEXT'],
                'description' => $data['DETAIL_TEXT'],
            ];
        }
        if (!$video)
            \CHTTP::SetStatus(404);
        return ['videos' => $video];
    }
}
