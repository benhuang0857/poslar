<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number', 20)->unique()->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('dining_table_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('final_price', 10, 2);
            $table->boolean('paid')->default(false);
            $table->enum('status', ['process', 'pending', 'completed', 'cancelled', 'delivered'])->default('process');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->integer('quantity')->default(1);
            $table->string('item')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->enum('status', ['process', 'pending', 'completed', 'cancelled'])->default('process');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
