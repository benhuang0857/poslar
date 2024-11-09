<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionType;
use Illuminate\Support\Facades\DB;

class ProductOptionTypesProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $optionTypes = ProductOptionType::all();

        foreach ($products as $product) {
            $randomOptionTypes = $optionTypes->random(rand(1, $optionTypes->count()));

            foreach ($randomOptionTypes as $optionType) {
                DB::table('product_option_types_products')->insert([
                    'product_id' => $product->id,
                    'product_option_type_id' => $optionType->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
