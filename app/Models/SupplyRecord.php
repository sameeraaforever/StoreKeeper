<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplyRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id','location_id','product_id',
        'quantity','unit_price','supply_date',
        'created_by','updated_by','deleted_by'
    ];

    protected $dates = ['supply_date'];

    // Remove auto-calc, DB handles it
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = auth()->id();
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });

        static::deleting(function ($model) {
            $model->deleted_by = auth()->id();
            $model->save();
        });
    }

    // Relations
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }
}
