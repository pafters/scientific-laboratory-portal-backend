<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\System\ImageManager;

final class CourseManager
{
    public function __construct(
        private DbManager $dbManager,
        private readonly ImageManager $imageManager,
    ) {}

    public function getAll(string $page, string $counts, ?string $age): array
    {
        $courses = [];

        if (filter_var($page, FILTER_VALIDATE_INT) && filter_var($counts, FILTER_VALIDATE_INT)) {
            $data = $this->dbManager->getAllCourses($counts, $page, $age);
            while ($course = $data->Fetch()) {
                $courses[] = [
                    'id' => $course['ID'],
                    'name' => $course['NAME'],
                    'date_start' => $course['ACTIVE_FROM'],
                    'date_end' => $course['ACTIVE_TO'],
                    'detail_picture' => [
                        'big' => $this->imageManager->resizeImage($course['PREVIEW_PICTURE'] ?? $course['DETAIL_PICTURE'], 'courses', 'big_item'),
                        'small' => $this->imageManager->resizeImage($course['PREVIEW_PICTURE'] ?? $course['DETAIL_PICTURE'], 'courses', 'small_item'),
                    ],
                    'important' => null !== $course['PROPERTY_IMPORTANT_VALUE'],
                ];
            }
        }

        return ['courses' => $courses];
    }

    public function getOneById(string $id): array
    {
        $course = null;

        $data = $this->dbManager->getCourseById($id);
        if ($data) {
            $course = [
                'id' => $data['ID'],
                'name' => $data['NAME'],
                'date_start' => $data['ACTIVE_FROM'],
                'date_end' => $data['ACTIVE_TO'],
                'detail_picture' => $this->imageManager->resizeImage($data['DETAIL_PICTURE'], 'detail'),
                'detail_text' => $data['DETAIL_TEXT'],
                'photos' => $this->getPhotos($data['PROPERTY_PHOTOS_VALUE']),
            ];
        }
        if (!$course) {
            \CHTTP::SetStatus(404);
        }

        return ['course' => $course];
    }

    private function getPhotos(array $photos): array
    {
        $photo_paths = [];

        foreach ($photos as $elementId) {
            $resizeImage = $this->imageManager->resizeImage($elementId, 'gallery', 'small_item');
            $clearImage = $this->imageManager->resizeImage($elementId, 'none');
            $photo_paths[] = [
                'small' => $resizeImage,
                'big' => $clearImage,
            ];
        }

        return $photo_paths;
    }
}
