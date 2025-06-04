<?php

namespace App\Enum;

enum Status: string
{
    case PRESENT = 'present';
    case ABSENT = 'absent';
    case LATE = 'late';
    case JUSTIFIED = 'justified';
}
