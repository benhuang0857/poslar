<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    use HasFactory;

    protected $table = 'product_option_values';

    protected $fillable = [
        'product_option_type_id',
        'value',
        'image',
        'enable_stock',
        'stock',
        'enable_price',
        'price'
    ];

    public function optionType()
    {
        return $this->belongsTo(ProductOptionType::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_option_values_products', 'product_option_value_id', 'product_id');
    }

    public function skus()
    {
        return $this->belongsToMany(Sku::class, 'product_option_values_skus');
    }
}
