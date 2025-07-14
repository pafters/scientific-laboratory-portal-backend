<?php

declare(strict_types=1);

namespace Phosagro\Object;

enum TaskType: string
{
    case BASE_TASK = 'base_task';
    case FILL_OUT_THE_FORM = 'fill_out_the_form';
    case UPLOAD_FILE = 'upload_file';
    case VISIT_THE_PLACE = 'visit_the_place';
    case WATCH_THE_VIDEO = 'watch_the_video';
}
