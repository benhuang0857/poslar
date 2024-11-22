<?php

namespace App\Models\Cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionValue;

class CartItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'serial_number',
        'cart_id',
        'product_id',
        'quantity',
        'price',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // option
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function options()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'cart_item_product_option_value', 'cart_item_id', 'product_option_value_id');
    }
}
