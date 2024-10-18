<?php

namespace App\Enum;

enum Status: string
{
    case Created = 'Created';
    case Paid = 'Paid';
    case Delivered = 'Delivered';
    case Cancelled = 'Cancelled';
    case Deleted = 'Deleted';
}
