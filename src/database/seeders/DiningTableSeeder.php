<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Store\DiningTable;

class DiningTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiningTable::create(['name' => '餐桌01', 'quantity' => 4 ]);
        DiningTable::create(['name' => '餐桌02', 'quantity' => 4 ]);
        DiningTable::create(['name' => '餐桌03', 'quantity' => 10]);
    }
}
