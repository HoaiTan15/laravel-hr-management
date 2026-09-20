<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
}
