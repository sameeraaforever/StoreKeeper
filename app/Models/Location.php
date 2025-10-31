<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = ['company_id','address_line','city','state','zip_code','country'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplyRecords()
    {
        return $this->hasMany(SupplyRecord::class);
    }
}
