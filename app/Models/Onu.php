<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'olt_id',
        'fat_id',
        'onu_sn',
        'onu_index',
        'pon_port',
        'model',
        'customer_label',
        'status',
        'rx_power_dbm',
        'tx_power_dbm',
        'last_seen_at',
        'provisioned_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'rx_power_dbm' => 'decimal:3',
        'tx_power_dbm' => 'decimal:3',
        'last_seen_at' => 'datetime',
        'provisioned_at' => 'datetime',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(Olt::class);
    }

    public function fat(): BelongsTo
    {
        return $this->belongsTo(Fat::class);
    }
}
