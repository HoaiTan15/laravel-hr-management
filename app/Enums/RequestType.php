<?php

namespace App\Enums;

enum RequestType: string
{
    case HARDWARE = 'hardware';
    case SOFTWARE = 'software';
    case ACCOUNT = 'account';
    case OTHER = 'other';
    case PROFILE_CHANGE = 'profile_change';
}
