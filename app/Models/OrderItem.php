<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',       // Allow mass assignment for 'order_id'
        'product_id',     // Allow mass assignment for 'product_id'
        'price',          // Allow mass assignment for 'price'
        'quantity',       // Allow mass assignment for 'quantity'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
