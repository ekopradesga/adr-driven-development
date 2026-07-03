<?php

namespace App\Models;

use App\Enums\OdfStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Odf extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'odf_code',
        'name',
        'location_name',
        'latitude',
        'longitude',
        'status',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'status' => OdfStatus::class,
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function fats(): HasMany
    {
        return $this->hasMany(Fat::class);
    }
}
