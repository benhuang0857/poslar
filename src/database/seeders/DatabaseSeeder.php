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
        // php artisan db:seed
        // User::factory(10)->create();

        User::factory()->create([
            "name" => "Test User",
            "email" => "test@example.com",
        ]);

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
        ]);
    }
}
