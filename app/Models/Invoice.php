<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Invoice — Billing module (stub).
 * Full Invoice module implementation is a separate story.
 */
class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'subscription_id', 'status', 'amount'];
}
