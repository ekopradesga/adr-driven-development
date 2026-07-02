<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Package — Customer Management module (stub).
 * Full Package module implementation is a separate story.
 */
class Package extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'monthly_price', 'downstream_kbps', 'upstream_kbps'];
}
