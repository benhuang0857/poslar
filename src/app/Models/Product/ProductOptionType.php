<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionType extends Model
{
    use HasFactory;

    protected $table = 'product_option_types';

    protected $fillable = [
        'name',
        'image',
        'enable_multi_select'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_option_types_products', 'product_option_type_id', 'product_id');
    }

    public function optionValues()
    {
        return $this->hasMany(ProductOptionValue::class, 'product_option_type_id', 'id');
    }
}
