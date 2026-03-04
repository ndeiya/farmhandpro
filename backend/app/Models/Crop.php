<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Crop extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farm_id',
        'name',
        'variety',
        'acres',
        'planted_date',
        'expected_harvest',
        'status',
        'yield_estimate',
    ];

    protected $casts = [
        'planted_date' => 'date',
        'expected_harvest' => 'date',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
