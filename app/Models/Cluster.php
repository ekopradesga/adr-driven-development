<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Cluster — Customer Management module (Aggregate Root, stub).
 * Full Cluster module implementation is a separate story.
 */
class Cluster extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'status', 'notes'];
}
