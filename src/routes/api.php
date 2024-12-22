<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\CustomerController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductOptionTypeController;
use App\Http\Controllers\Product\ProductOptionValueController;
use App\Http\Controllers\Product\SKUController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CartItemController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Store\StoreController;
use App\Http\Controllers\Store\DiningTableController;
use App\Http\Controllers\Store\PaymentController;
use App\Http\Controllers\Store\PromotionController;

/**
 * 動態註冊公共路由
 *
 * @param string $prefix 路由前綴
 * @param array $middleware 中間件
 */
function registerRoutes($prefix = '', $middleware = [])
{
    Route::prefix($prefix)->middleware($middleware)->group(function () {
        // User routes
        Route::get('users', [UserController::class, 'all']);
        Route::get('users/{id}', [UserController::class, 'show']);
        Route::post('users', [UserController::class, 'store']);
        Route::post('users/{id}', [UserController::class, 'update']);
        Route::delete('users', [UserController::class, 'destroy']);

        // Customer routes
        Route::get('customer', [CustomerController::class, 'all']);
        Route::get('customer/{id}', [CustomerController::class, 'show']);
        Route::post('customer', [CustomerController::class, 'store']);
        Route::post('customer/{id}', [CustomerController::class, 'update']);
        Route::delete('customer', [CustomerController::class, 'destroy']);

        // Product routes
        Route::get('products', [ProductController::class, 'all']);
        Route::get('products/{id}', [ProductController::class, 'show']);
        Route::post('products', [ProductController::class, 'store']);
        Route::post('products/{id}', [ProductController::class, 'update']);
        Route::delete('products', [ProductController::class, 'destroy']);

        // Product Category routes
        Route::get('product-category', [ProductCategoryController::class, 'all']);
        Route::get('product-category/{id}', [ProductCategoryController::class, 'show']);
        Route::post('product-category', [ProductCategoryController::class, 'store']);
        Route::post('product-category/{id}', [ProductCategoryController::class, 'update']);
        Route::delete('product-category', [ProductCategoryController::class, 'destroy']);

        // Product Option Type routes
        Route::get('product-option-types', [ProductOptionTypeController::class, 'all']);
        Route::get('product-option-types/{id}', [ProductOptionTypeController::class, 'show']);
        Route::post('product-option-types', [ProductOptionTypeController::class, 'store']);
        Route::post('product-option-types/{id}', [ProductOptionTypeController::class, 'update']);
        Route::delete('product-option-types', [ProductOptionTypeController::class, 'destroy']);

        // Product Option Value routes
        Route::get('product-option-values', [ProductOptionValueController::class, 'all']);
        Route::get('product-option-values/{id}', [ProductOptionValueController::class, 'show']);
        Route::post('product-option-values', [ProductOptionValueController::class, 'store']);
        Route::post('product-option-values/{id}', [ProductOptionValueController::class, 'update']);
        Route::delete('product-option-values', [ProductOptionValueController::class, 'destroy']);

        // SKU routes
        Route::get('skus', [SKUController::class, 'all']);
        Route::get('skus/{id}', [SKUController::class, 'show']);
        Route::post('skus', [SKUController::class, 'store']);
        Route::post('skus/{id}', [SKUController::class, 'update']);
        Route::delete('skus', [SKUController::class, 'destroy']);
        Route::get('skus/gen-skus/{id}', [SKUController::class, 'genSKUs']);

        // Store routes
        Route::get('store/{id}', [StoreController::class, 'show']);
        Route::post('store', [StoreController::class, 'store']);
        Route::post('store/{id}', [StoreController::class, 'update']);

        // Dining Table routes
        Route::get('dining-table', [DiningTableController::class, 'all']);
        Route::get('dining-table/{id}', [DiningTableController::class, 'show']);
        Route::post('dining-table', [DiningTableController::class, 'store']);
        Route::post('dining-table/{id}', [DiningTableController::class, 'update']);
        Route::delete('dining-table', [DiningTableController::class, 'destroy']);

        // Payment routes
        Route::get('payment', [PaymentController::class, 'all']);
        Route::get('payment/{id}', [PaymentController::class, 'show']);
        Route::post('payment', [PaymentController::class, 'store']);
        Route::post('payment/{id}', [PaymentController::class, 'update']);
        Route::delete('payment', [PaymentController::class, 'destroy']);

        // Promotion routes
        Route::get('promotion', [PromotionController::class, 'all']);
        Route::get('promotion/{id}', [PromotionController::class, 'show']);
        Route::post('promotion', [PromotionController::class, 'store']);
        Route::post('promotion/{id}', [PromotionController::class, 'update']);
        Route::delete('promotion', [PromotionController::class, 'destroy']);

        // Order routes
        Route::get('order', [OrderController::class, 'all']);
        Route::get('order/{serial_number}', [OrderController::class, 'show']);
        Route::post('order', [OrderController::class, 'store']);
        Route::post('order/{serial_number}', [OrderController::class, 'update']);
        Route::delete('order', [OrderController::class, 'destroy']);
        Route::get('kitch', [OrderController::class, 'get_kitch_today_order']);
        Route::post('kitch/{id}', [OrderController::class, 'update_kitch_order']);
    });
}

registerRoutes();

registerRoutes('admin');