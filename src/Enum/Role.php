<?php

namespace App\Enum;

enum Role: string
{
    case ROLE_USER = 'ROLE_USER';
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_VIEW = 'ROLE_VIEW';
    case ROLE_DEV = 'ROLE_DEV';
}
