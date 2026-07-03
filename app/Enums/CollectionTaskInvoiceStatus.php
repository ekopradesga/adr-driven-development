<?php

namespace App\Enums;

enum CollectionTaskInvoiceStatus: string
{
    case Created = 'created';
    case Resolved = 'resolved';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Resolved => 'Resolved',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Created => 'secondary',
            self::Resolved => 'success',
            self::Cancelled => 'dark',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Resolved, self::Cancelled], true);
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