<?php

declare(strict_types=1);

namespace Phosagro\Enum;

enum UserGroupType: string
{
    case EMPLOYEES = 'employees';
    case SCHOOLCHILDREN = 'schoolchildren';
    case STUDENTS = 'students';
}
