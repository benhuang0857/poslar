<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\ProductOptionType;

class ProductOptionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductOptionType::create(['name' => '前菜', 'enable_multi_select' => false]);
        ProductOptionType::create(['name' => '主餐', 'enable_multi_select' => false]);
        ProductOptionType::create(['name' => '點心', 'enable_multi_select' => true]);
    }
}
