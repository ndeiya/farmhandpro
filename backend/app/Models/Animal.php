<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farm_id',
        'type',
        'count',
        'breed',
        'age_group',
        'status',
        'notes',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
