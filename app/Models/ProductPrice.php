<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPrice extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id','price','start_date','end_date'];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    public function getByProduct($productId)
    {
        $price = ProductPrice::where('product_id', $productId)
            ->where(function($q){
                $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->orderBy('start_date', 'desc')
            ->first();

        if (!$price) {
            return response()->json(['price' => null, 'message' => 'No price found'], 404);
        }

        return response()->json(['price' => $price->price]);
    }


}
