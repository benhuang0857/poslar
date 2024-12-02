<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create(['name' => '商業套餐', 'price' => 500, 'enable_stock' => false, 'stock' => -999, 'description' => '商業套餐']);
        Product::create(['name' => '主廚推薦', 'price' => 900, 'enable_stock' => true, 'stock' => 25, 'description' => '主廚推薦']);
        Product::create(['name' => '精選推薦', 'price' => 1800, 'enable_stock' => true, 'stock' => 25, 'description' => '精選推薦']);
        Product::create(['name' => '菲力單點', 'price' => 500, 'enable_stock' => true, 'stock' => 25, 'description' => '菲力單點']);
        Product::create(['name' => '龍蝦單點', 'price' => 500, 'enable_stock' => true, 'stock' => 25, 'description' => '龍蝦單點']);
    }
}
