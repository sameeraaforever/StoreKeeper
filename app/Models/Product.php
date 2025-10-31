<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['name','category_id','unit','description'];

    
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
}
