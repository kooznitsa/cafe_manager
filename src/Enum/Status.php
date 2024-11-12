<?php

namespace App\Enum;

use Symfony\Component\Validator\Exception\ValidatorException;

enum Status: string
{
    case Created = 'Created';
    case Paid = 'Paid';
    case Delivered = 'Delivered';
    case Cancelled = 'Cancelled';
    case Deleted = 'Deleted';

    public static function validate(?string $item): bool
    {
        if (!empty($item) && !self::tryFrom($item) instanceof self) {
            throw new ValidatorException($item . ' is not a valid backing value for enum ' . self::class);
        }

        return true;
    }
}
