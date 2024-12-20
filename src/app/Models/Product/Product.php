<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'enable_adv_sku',
        'sku',
        'feature_image',
        'price',
        'enable_stock',
        'stock',
        'description',
        'status',
    ];

    public function optionTypes()
    {
        return $this->belongsToMany(ProductOptionType::class, 'product_option_types_products', 'product_id', 'product_option_type_id');
    }

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_option_values_products', 'product_id', 'product_option_value_id');
    }

    public function skus()
    {
        return $this->hasMany(Sku::class);
    }

    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class, 'product_category_relation');
    }
}
