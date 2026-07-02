<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentAllocation — Billing module (stub).
 * Full implementation is part of the Payment module story.
 */
class PaymentAllocation extends Model
{
    protected $fillable = ['payment_id', 'invoice_id', 'amount'];
}
