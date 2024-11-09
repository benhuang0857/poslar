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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('enable_adv_sku')->default(false);
            $table->string('sku')->nullable();
            $table->string('feature_image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('enable_stock')->default(false);
            $table->integer('stock')->default(-999);
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
        
        Schema::create('product_option_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->boolean('enable_multi_select')->default(false);
            $table->timestamps();
        });
        
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_type_id')->constrained('product_option_types')->onDelete('cascade');
            $table->string('value'); //選項值名稱（如紅色、L號）
            $table->string('image')->nullable();
            $table->boolean('enable_stock')->default(false);
            $table->integer('stock')->default(-999);
            $table->boolean('enable_price')->default(false);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
        
        Schema::create('product_option_types_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_option_type_id')->constrained('product_option_types')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('product_option_values_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_option_value_id')->constrained('product_option_values')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku')->nullable();
            $table->boolean('enable_stock')->default(false);
            $table->integer('stock')->default(-999);
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
        
        Schema::create('product_option_values_skus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sku_id')->constrained('skus')->onDelete('cascade');
            $table->foreignId('product_option_value_id')->constrained('product_option_values')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('product_category_relation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('product_category_id')->constrained('product_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_option_values_skus');
        Schema::dropIfExists('skus');
        Schema::dropIfExists('product_option_values_products');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_option_types');
        Schema::dropIfExists('product_category_relation');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('products');
    }
};
