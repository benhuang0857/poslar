<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    use HasFactory;

    protected $table = 'skus';

    protected $fillable = [
        'product_id',
        'sku',
        'stock',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'product_option_values_skus');
    }
}
