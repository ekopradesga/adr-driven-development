<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => EmployeeStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collectionTasks(): HasMany
    {
        return $this->hasMany(CollectionTask::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', EmployeeStatus::Active->value);
    }

    public function isActive(): bool
    {
        return $this->status === EmployeeStatus::Active;
    }

    public function isInactive(): bool
    {
        return $this->status === EmployeeStatus::Inactive;
    }

    public function isArchived(): bool
    {
        return $this->status === EmployeeStatus::Archived;
    }
}