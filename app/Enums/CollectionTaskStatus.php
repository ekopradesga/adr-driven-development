<?php

namespace App\Enums;

enum CollectionTaskStatus: string
{
    case WaitingAssignment = 'waiting_assignment';
    case Assigned = 'assigned';
    case Scheduled = 'scheduled';
    case OnRoute = 'on_route';
    case CustomerVisited = 'customer_visited';
    case Completed = 'completed';
    case FollowUpRequired = 'follow_up_required';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WaitingAssignment => 'Waiting Assignment',
            self::Assigned => 'Assigned',
            self::Scheduled => 'Scheduled',
            self::OnRoute => 'On Route',
            self::CustomerVisited => 'Customer Visited',
            self::Completed => 'Completed',
            self::FollowUpRequired => 'Follow Up Required',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::WaitingAssignment => 'secondary',
            self::Assigned => 'info',
            self::Scheduled => 'primary',
            self::OnRoute => 'warning',
            self::CustomerVisited => 'warning',
            self::Completed => 'success',
            self::FollowUpRequired => 'danger',
            self::Cancelled => 'dark',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::FollowUpRequired, self::Cancelled], true);
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