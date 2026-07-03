<?php

namespace App\Enums;

/**
 * PaymentStatus — Payments module.
 *
 * Canonical values follow docs/architecture/decisions.md
 * Decision: Payment Lifecycle Canonical States.
 */
enum PaymentStatus: string
{
    case IntentCreated      = 'intent_created';
    case WaitingPayment     = 'waiting_payment';
    case Received           = 'received';
    case Validated          = 'validated';
    case Recorded           = 'recorded';
    case PartiallyAllocated = 'partially_allocated';
    case FullyAllocated     = 'fully_allocated';
    case Completed          = 'completed';
    case Reversed           = 'reversed';
    case Failed             = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::IntentCreated      => 'Intent Created',
            self::WaitingPayment     => 'Waiting Payment',
            self::Received           => 'Received',
            self::Validated          => 'Validated',
            self::Recorded           => 'Recorded',
            self::PartiallyAllocated => 'Partially Allocated',
            self::FullyAllocated     => 'Fully Allocated',
            self::Completed          => 'Completed',
            self::Reversed           => 'Reversed',
            self::Failed             => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::IntentCreated      => 'secondary',
            self::WaitingPayment     => 'info',
            self::Received           => 'primary',
            self::Validated          => 'primary',
            self::Recorded           => 'primary',
            self::PartiallyAllocated => 'warning',
            self::FullyAllocated     => 'info',
            self::Completed          => 'success',
            self::Reversed           => 'dark',
            self::Failed             => 'danger',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Reversed, self::Failed], true);
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
