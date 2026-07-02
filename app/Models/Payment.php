<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Payment — Payments module (stub).
 * Full Payment module implementation is a separate story.
 */
class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'amount', 'status'];
}
