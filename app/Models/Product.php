<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','category_id','unit','description','stock_qty'];


    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function supplyRecords()
    {
        return $this->hasMany(SupplyRecord::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function activePrice()
    {
        return $this->hasOne(\App\Models\ProductPrice::class)
                    ->where(function($q){
                        $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now()->toDateString());
                    })->orderByDesc('start_date');
    }
}
