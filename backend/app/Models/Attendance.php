<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'clock_in_time',
        'clock_out_time',
        'date',
        'notes',
        'status',
    ];

    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getHoursWorkedAttribute()
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            return $this->clock_out_time->diffInHours($this->clock_in_time);
        }
        return 0;
    }
}
