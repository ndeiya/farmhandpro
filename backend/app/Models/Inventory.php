<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'farm_id',
        'name',
        'category',
        'quantity',
        'unit',
        'reorder_level',
        'last_updated',
        'notes',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
