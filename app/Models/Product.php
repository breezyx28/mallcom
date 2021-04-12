<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $appends = ['final_price'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function store()
    {
        return $this->belongsTo(StoreProduct::class, 'id', 'product_id');
    }

    public function additional_description()
    {
        return $this->hasOne(AdditionalDescription::class, 'product_id', 'id');
    }

    public function rate()
    {
        return $this->hasMany(Rate::class, 'product_id', 'id');
    }

    public function product_photos()
    {
        return $this->hasMany(ProductsPhoto::class, 'product_id', 'id');
    }
    public function product_sizes()
    {
        return $this->hasMany(ProductSizes::class, 'product_id', 'id');
    }

    public function getPhotoAttribute($value)
    {
        // replace http://localhost to by htpp://127.0.0.1
        $base_url = str_replace('localhost', env('DB_HOST'), env('APP_URL'));

        return $base_url . ':' . $_SERVER['SERVER_PORT'] . "/storage/" . $value;
    }

    public function getFinalPriceAttribute()
    {
        $price = $this->attributes['price'];
        $addPrice = 0;
        $discount = 0;

        try {
            $addPrice = $this->attributes['addetionalPrice'];
        } catch (\Throwable $th) {
            $addPrice = 0;
        }

        try {
            $discount = $this->attributes['discount'];
        } catch (\Throwable $th) {
            $discount = 0;
        }

        $discountedValue = ($price * ($discount / 100)) + ((int) $addPrice);

        return ($price - $discountedValue);
    }
}
