<?php

namespace App\Enums;

enum SettingScope: string
{
    case Global   = 'global';
    case Area     = 'area';
    case Cluster  = 'cluster';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Global   => 'Global',
            self::Area     => 'Service Area',
            self::Cluster  => 'Cluster',
            self::Customer => 'Customer',
        };
    }
}
