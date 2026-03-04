<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farm extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'owner_id',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'size_acres',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function crops()
    {
        return $this->hasMany(Crop::class);
    }

    public function animals()
    {
        return $this->hasMany(Animal::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
