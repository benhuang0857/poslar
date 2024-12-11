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
            $table->string('shipping')->nullable();
            $table->enum('status', ['process', 'pending', 'completed', 'cancelled', 'delivered'])->default('process');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->string('product_name')->nullable();
            $table->string('product_option')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['process', 'pending', 'completed', 'cancelled', 'delivered'])->default('process');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });

        Schema::create('order_item_product_option_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_option_value_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_product_option_value');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
