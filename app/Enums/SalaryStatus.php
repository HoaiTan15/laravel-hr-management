<?php

namespace App\Enums;

enum SalaryStatus: string
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case PAID = 'paid';
}
