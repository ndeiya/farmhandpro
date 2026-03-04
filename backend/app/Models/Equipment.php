<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farm_id',
        'name',
        'type',
        'serial_number',
        'purchase_date',
        'status',
        'last_maintenance',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance' => 'date',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
