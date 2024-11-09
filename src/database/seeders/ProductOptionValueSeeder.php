<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\ProductOptionType;
use App\Models\Product\ProductOptionValue;

class ProductOptionValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstMeal = ProductOptionType::where('name', '前菜')->first();
        $secondMeal = ProductOptionType::where('name', '主餐')->first();
        $thirdMeal = ProductOptionType::where('name', '點心')->first();

        ProductOptionValue::create(['product_option_type_id' => $firstMeal->id, 'value' => '主廚濃湯', 
        'enable_stock' => true, 'stock' => 300, 'enable_price' => true, 'price' => 50]);
        ProductOptionValue::create(['product_option_type_id' => $firstMeal->id, 'value' => '海鮮濃湯', 
        'enable_stock' => true, 'stock' => 200, 'enable_price' => true, 'price' => 150]);

        ProductOptionValue::create(['product_option_type_id' => $secondMeal->id, 'value' => '雞排']);
        ProductOptionValue::create(['product_option_type_id' => $secondMeal->id, 'value' => '豬排']);
        ProductOptionValue::create(['product_option_type_id' => $secondMeal->id, 'value' => '老饕上蓋']);

        ProductOptionValue::create(['product_option_type_id' => $thirdMeal->id, 'value' => '咖啡']);
        ProductOptionValue::create(['product_option_type_id' => $thirdMeal->id, 'value' => '奶茶']);
    }
}
