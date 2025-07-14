<?php

declare(strict_types=1);

namespace Phosagro\Manager;

use Phosagro\Iblocks;

final class DbManager
{
    static function getAllCourses(string|int $counts, string|int $page, string|null $age)
    {

        $navParams = [
            'nPageSize' => $counts,
            'iNumPage' => $page,
            'bShowAll' => false,
            'checkOutOfRange' => true
        ];

        $res = \CIBlockElement::GetList(
            [
                'ACTIVE_FROM' => 'DESC'
            ],
            [
                'IBLOCK_ID' => Iblocks::courseId(),
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'PROPERTY_AGE_CATEGORY.SORT' => $age,
            ],
            false,
            $navParams,
            [
                'ID',
                'NAME',
                'PROPERTY_AGE_CATEGORY',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'DETAIL_PICTURE',
                'PREVIEW_PICTURE',
                'PROPERTY_IMPORTANT'
            ]
        );

        return $res;
    }

    static function getAllContacts()
    {
        $res = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => Iblocks::contactsId(),
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_CITY_CATEGORY',
                'PROPERTY_ADDRESS',
                'PROPERTY_PROPERTY_PHONE',
                'PROPERTY_PROPERTY_SITE',
                'PROPERTY_SITE_NAME',
                'PROPERTY_PROPERTY_LONGITUDE',
                'PROPERTY_PROPERTY_LATITUDE',
                'PROPERTY_PROPERTY_SCHEDULE',
                'PREVIEW_PICTURE',
            ]
        );

        return $res;
    }

    static function getAllFeeds(string $counts, string $page, string|null $city)
    {
        $ntopcount = 0;
        $noffset = 0;
        if ($page <= 1) {
            $ntopcount = $counts + 1;
            $noffset = 0;
        } else {
            $ntopcount = $counts;
            $noffset = (($page - 1) * $counts) + 1;
        }
        //var_export([$ntopcount, $noffset]);
        $navParams = [
            'nOffset' => $noffset,
            'nTopCount' => $ntopcount,
            'bShowAll' => false,
            'checkOutOfRange' => true
        ];

        $filter = [
            'IBLOCK_ID' => Iblocks::newsId(),
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
        ];

        $city = ((null === $city) ? '' : trim($city));

        if ('' !== $city) {
            $filter[] = [
                'LOGIC' => 'OR',
                'PROPERTY_CITY' => false,
                'PROPERTY_CITY.CODE' => $city,
            ];
        }

        $res = \CIBlockElement::GetList(
            [
                'ACTIVE_FROM' => 'DESC'
            ],
            $filter,
            false,
            $navParams,
            [
                'ID',
                'NAME',
                'PROPERTY_CITY',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'PREVIEW_TEXT',
                'PROPERTY_IMPORTANT'
            ]
        );

        return $res;
    }

    static function getAllVideos(string $counts, string $page)
    {
        $navParams = [
            'nPageSize' => $counts,
            'iNumPage' => $page,
            'bShowAll' => false,
            'checkOutOfRange' => true
        ];

        $res = \CIBlockElement::GetList(
            [
                'ACTIVE_FROM' => 'DESC'
            ],
            [
                'IBLOCK_ID' => Iblocks::videoId(),
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
            ],
            false,
            $navParams,
            [
                'ID',
                'NAME',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'PREVIEW_PICTURE',
                'PREVIEW_TEXT',
            ]
        );

        return $res;
    }

    static function getCourseById(string $courseId)
    {
        $res = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => Iblocks::courseId(),
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'ID' => $courseId
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_AGE_CATEGORY',
                'PROPERTY_PHOTOS',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
                'PROPERTY_IMPORTANT'
            ]
        );

        return $res->Fetch();
    }

    static function getFeedById(string $feedId)
    {
        $res = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => Iblocks::newsId(),
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'ID' => $feedId
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_CITY',
                'PROPERTY_PHOTOS',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
                'PROPERTY_IMPORTANT'
            ]
        );

        return $res->Fetch();
    }

    static function getVideoById(string $videoId)
    {
        $res = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => Iblocks::videoId(),
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'ID' => $videoId
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'ACTIVE_FROM',
                'ACTIVE_TO',
                'PREVIEW_PICTURE',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
            ]
        );

        return $res->Fetch();
    }
}
