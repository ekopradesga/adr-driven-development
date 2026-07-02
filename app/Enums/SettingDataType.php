<?php

namespace App\Enums;

enum SettingDataType: string
{
    case String  = 'string';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Json    = 'json';
    case Decimal = 'decimal';

    public function label(): string
    {
        return match ($this) {
            self::String  => 'String',
            self::Integer => 'Integer',
            self::Boolean => 'Boolean',
            self::Json    => 'JSON',
            self::Decimal => 'Decimal',
        };
    }
}
