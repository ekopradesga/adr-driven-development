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

    public function badgeColor(): string
    {
        return match ($this) {
            self::Allocated => 'success',
            self::Reversed  => 'dark',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Reversed;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }
}
