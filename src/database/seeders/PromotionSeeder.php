<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store\Promotion;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promotion::create(['name' => '促銷01', 'discount' => 0.5]);
        Promotion::create(['name' => '促銷02', 'discount' => 0.6]);
    }
}
