<?php

namespace App\Enums;

enum TaskStatus: string
{
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case STOPPED = 'stopped';
}
