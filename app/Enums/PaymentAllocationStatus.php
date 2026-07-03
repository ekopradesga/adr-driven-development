<?php

namespace App\Enums;

/**
 * PaymentAllocationStatus — Payments module.
 */
enum PaymentAllocationStatus: string
{
    case Allocated = 'allocated';
    case Reversed  = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::Allocated => 'Allocated',
            self::Reversed  => 'Reversed',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
