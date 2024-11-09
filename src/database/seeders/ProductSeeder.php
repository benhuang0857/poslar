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
        Product::create(['name' => '商業午餐套餐', 'price' => 500, 'enable_stock' => false, 'stock' => -999, 'description' => '商業午餐']);
        Product::create(['name' => '主廚推薦套餐', 'price' => 900, 'enable_stock' => true, 'stock' => 25, 'description' => '主廚推薦']);
        Product::create(['name' => '頂級饗宴套餐', 'price' => 1500, 'enable_stock' => true, 'stock' => 15, 'description' => '頂級饗宴']);
    }
}
