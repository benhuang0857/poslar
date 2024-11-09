<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionType;
use App\Models\Product\ProductOptionValue;
use Illuminate\Support\Facades\DB;

class ProductOptionValuesProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {
            $relatedOptionTypes = $product->optionTypes;

            foreach ($relatedOptionTypes as $optionType) {
                $optionValues = ProductOptionValue::where('product_option_type_id', $optionType->id)->get();

                foreach ($optionValues as $optionValue) {
                    DB::table('product_option_values_products')->insert([
                        'product_id' => $product->id,
                        'product_option_value_id' => $optionValue->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
