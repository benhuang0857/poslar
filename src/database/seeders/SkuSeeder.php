<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\Product;
use App\Models\Product\Sku;

class SkuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            Sku::create([
                'product_id' => $product->id,
                'sku' => 'SKU-' . strtoupper(uniqid()),
                'stock' => $product->stock,
                'price' => $product->price,
            ]);
        }
    }
}
