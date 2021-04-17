<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdersNumber extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->hasMany(Order::class, 'orders_number_id', 'id');
    }
}
