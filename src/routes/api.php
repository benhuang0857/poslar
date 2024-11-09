<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductOptionTypeController;
use App\Http\Controllers\Product\ProductOptionValueController;
use App\Http\Controllers\Product\SKUController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CartItemController;
use App\Http\Controllers\Store\StoreController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('users', UserController::class);

Route::get('products', [ProductController::class, 'all']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::post('products', [ProductController::class, 'store']);
Route::post('products/{id}', [ProductController::class, 'update']);
Route::delete('products', [ProductController::class, 'destroy']);

Route::get('product-category', [ProductCategoryController::class, 'all']);
Route::get('product-category/{id}', [ProductCategoryController::class, 'show']);
Route::post('product-category', [ProductCategoryController::class, 'store']);
Route::post('product-category/{id}', [ProductCategoryController::class, 'update']);
Route::delete('product-category', [ProductCategoryController::class, 'destroy']);

Route::get('product-option-types', [ProductOptionTypeController::class, 'all']);
Route::get('product-option-types/{id}', [ProductOptionTypeController::class, 'show']);
Route::post('product-option-types', [ProductOptionTypeController::class, 'store']);
Route::post('product-option-types/{id}', [ProductOptionTypeController::class, 'update']);
Route::delete('product-option-types', [ProductOptionTypeController::class, 'destroy']);

Route::get('product-option-values', [ProductOptionValueController::class, 'all']);
Route::get('product-option-values/{id}', [ProductOptionValueController::class, 'show']);
Route::post('product-option-values', [ProductOptionValueController::class, 'store']);
Route::post('product-option-values/{id}', [ProductOptionValueController::class, 'update']);
Route::delete('product-option-values', [ProductOptionValueController::class, 'destroy']);

Route::get('skus', [SKUController::class, 'all']);
Route::get('skus/{id}', [SKUController::class, 'show']);
Route::post('skus', [SKUController::class, 'store']);
Route::post('skus/{id}', [SKUController::class, 'update']);
Route::delete('skus', [SKUController::class, 'destroy']);
Route::get('skus/gen-skus/{id}', [SKUController::class, 'genSKUs']);

Route::get('store/{id}', [StoreController::class, 'show']);
Route::post('store', [StoreController::class, 'store']);
Route::post('store/{id}', [StoreController::class, 'update']);

Route::post('cart', [CartController::class, 'store']);
Route::get('cart/{serial_number}', [CartController::class, 'show']);
Route::post('cart/{serial_number}', [CartItemController::class, 'store']);
Route::post('cart/item/{id}', [CartItemController::class, 'update']);
Route::delete('cart/item/{id}', [CartItemController::class, 'destroy']);

// Route::post('cart-item/{id}', [CartItemController::class, 'update']);
// Route::post('cart-item/{id}', [CartItemController::class, 'destroy']);
