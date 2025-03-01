<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            ProductOptionTypeSeeder::class,
            ProductOptionValueSeeder::class,
            SkuSeeder::class,
            ProductCategorySeeder::class,
            ProductCategoryRelationSeeder::class,
            ProductOptionTypesProductsSeeder::class,
            ProductOptionValuesProductsSeeder::class,
            DiningTableSeeder::class,
            PaymentSeeder::class,
            PromotionSeeder::class,
            CustomerSeeder::class,
            UserSeeder::class,
        ]);
    }
}
