<?php

namespace App\Enums;

enum CollectionTaskPaymentSubmissionStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Acknowledged = 'acknowledged';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
            self::Acknowledged => 'Acknowledged',
            self::Failed => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'secondary',
            self::Submitted => 'info',
            self::Acknowledged => 'success',
            self::Failed => 'danger',
        };
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