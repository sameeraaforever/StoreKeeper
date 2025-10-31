<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','email','phone','status'];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function supplyRecords()
    {
        return $this->hasMany(SupplyRecord::class);
    }
}
