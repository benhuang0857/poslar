<?php

use Illuminate\Http\Request;
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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::apiResource('users', UserController::class);

Route::get('admin/users', [UserController::class, 'all']);
Route::get('users/{id}', [UserController::class, 'show']);
Route::post('users', [UserController::class, 'store']);
Route::post('users/{id}', [UserController::class, 'update']);
Route::delete('admin/users', [UserController::class, 'destroy']);

Route::get('admin/customer', [CustomerController::class, 'all']);
Route::get('customer/{id}', [CustomerController::class, 'show']);
Route::post('customer', [CustomerController::class, 'store']);
Route::post('customer/{id}', [CustomerController::class, 'update']);
Route::delete('admin/customer', [CustomerController::class, 'destroy']);

Route::get('products', [ProductController::class, 'all']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::post('admin/products', [ProductController::class, 'store']);
Route::post('admin/products/{id}', [ProductController::class, 'update']);
Route::delete('admin/products', [ProductController::class, 'destroy']);

Route::get('product-category', [ProductCategoryController::class, 'all']);
Route::get('product-category/{id}', [ProductCategoryController::class, 'show']);
Route::post('admin/product-category', [ProductCategoryController::class, 'store']);
Route::post('admin/product-category/{id}', [ProductCategoryController::class, 'update']);
Route::delete('admin/product-category', [ProductCategoryController::class, 'destroy']);

Route::get('product-option-types', [ProductOptionTypeController::class, 'all']);
Route::get('product-option-types/{id}', [ProductOptionTypeController::class, 'show']);
Route::post('admin/product-option-types', [ProductOptionTypeController::class, 'store']);
Route::post('admin/product-option-types/{id}', [ProductOptionTypeController::class, 'update']);
Route::delete('admin/product-option-types', [ProductOptionTypeController::class, 'destroy']);

Route::get('product-option-values', [ProductOptionValueController::class, 'all']);
Route::get('product-option-values/{id}', [ProductOptionValueController::class, 'show']);
Route::post('admin/product-option-values', [ProductOptionValueController::class, 'store']);
Route::post('admin/product-option-values/{id}', [ProductOptionValueController::class, 'update']);
Route::delete('admin/product-option-values', [ProductOptionValueController::class, 'destroy']);

Route::get('skus', [SKUController::class, 'all']);
Route::get('skus/{id}', [SKUController::class, 'show']);
Route::post('admin/skus', [SKUController::class, 'store']);
Route::post('admin/skus/{id}', [SKUController::class, 'update']);
Route::delete('admin/skus', [SKUController::class, 'destroy']);
Route::get('admin/skus/gen-skus/{id}', [SKUController::class, 'genSKUs']);

Route::get('store/{id}', [StoreController::class, 'show']);
Route::post('admin/store', [StoreController::class, 'store']);
Route::post('admin/store/{id}', [StoreController::class, 'update']);

Route::get('dining-table', [DiningTableController::class, 'all']);
Route::get('dining-table/{id}', [DiningTableController::class, 'show']);
Route::post('admin/dining-table', [DiningTableController::class, 'store']);
Route::post('admin/dining-table/{id}', [DiningTableController::class, 'update']);
Route::delete('admin/dining-table', [DiningTableController::class, 'destroy']);

Route::get('payment', [PaymentController::class, 'all']);
Route::get('payment/{id}', [PaymentController::class, 'show']);
Route::post('admin/payment', [PaymentController::class, 'store']);
Route::post('admin/payment/{id}', [PaymentController::class, 'update']);
Route::delete('admin/payment', [PaymentController::class, 'destroy']);

Route::get('promotion', [PromotionController::class, 'all']);
Route::get('promotion/{id}', [PromotionController::class, 'show']);
Route::post('admin/promotion', [PromotionController::class, 'store']);
Route::post('admin/promotion/{id}', [PromotionController::class, 'update']);
Route::delete('admin/promotion', [PromotionController::class, 'destroy']);

Route::get('order', [OrderController::class, 'all']);
Route::get('order/{serial_number}', [OrderController::class, 'show']);
Route::post('order', [OrderController::class, 'store']);
Route::post('order/{serial_number}', [OrderController::class, 'update']);
Route::delete('order', [OrderController::class, 'destroy']);

Route::get('kitch', [OrderController::class, 'get_kitch_today_order']);
Route::post('kitch/{id}', [OrderController::class, 'update_kitch_order']);

// useless
// Route::get('cart/{serial_number}', [CartController::class, 'show']);
// Route::post('cart', [CartController::class, 'store']);
// Route::put('cart/{serial_number}', [CartController::class, 'update']);
// Route::delete('cart/{serial_number}', [CartController::class, 'destroy']);

// Route::post('cart/{serial_number}/add', [CartItemController::class, 'store']);
// Route::put('cart/{serial_number}/item/{id}', [CartItemController::class, 'update']);
// Route::delete('cart/{serial_number}/item/{id}', [CartItemController::class, 'destroy']);
// Route::post('cart/{serial_number}/checkout', [CartController::class, 'checkout']);