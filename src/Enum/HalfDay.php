<?php

namespace App\Enum;

enum HalfDay: string
{
    case morning = '9-12';
    case afternoon = '13-16';
    case full_day = '9-16';
}
