<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;

class ProductCategoryRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainDish = ProductCategory::where('slug', 'bundle-dish')->first();
        $singleDish = ProductCategory::where('slug', 'single-dish')->first();

        Product::where('name', 'like', '%套餐')->each(function ($product) use ($mainDish) {
            $product->categories()->attach($mainDish);
        });
    }
}