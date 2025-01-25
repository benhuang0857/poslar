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
        Schema::create('store', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('opening_hours')->nullable();
            $table->json('social_links')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('dining_table', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('quantity')->defualt(0);
            $table->string('qrcode')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('payment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(false);
            $table->timestamps();
        });

        Schema::create('promotion', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->string('type')->default('numeric'); // numeric or percentage
            $table->boolean('enable_expired')->default('numeric');
            $table->date('start_time')->default('1900-01-01');
            $table->date('end_time')->default('1900-01-01');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store');
        Schema::dropIfExists('dining_table');
        Schema::dropIfExists('payment');
        Schema::dropIfExists('promotion');
    }
};
