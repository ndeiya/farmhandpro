<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'farm_id',
        'period_start',
        'period_end',
        'hours_worked',
        'hourly_rate',
        'gross_pay',
        'deductions',
        'net_pay',
        'status',
        'issued_date',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'issued_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
