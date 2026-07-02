<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ServiceArea — Customer Management module (Aggregate Root, stub).
 * Full ServiceArea module implementation is a separate story.
 */
class ServiceArea extends Model
{
    use SoftDeletes;

    protected $fillable = ['cluster_id', 'name', 'status', 'notes'];
}
