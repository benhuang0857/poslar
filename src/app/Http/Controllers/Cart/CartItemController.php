<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class CartItemController extends Controller
{

    public function store(Request $request, $serial_number)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'options' => 'nullable|array', // options 轉為 array
            ]);

            $cart = Cart::where('serial_number', $serial_number)->first();
            if (!$cart) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart not found']], 404);
            }

            $product = Product::where('id', $validated['product_id'])->first();
            $product_stock = $product->enable_stock ? $product->stock : -999;

            // 檢查主商品庫存
            if ($product_stock != -999 && $product->stock < $validated['quantity']) {
                return response()->json(['code' => 400, 'data' => ['message' => 'Add product failed. Out of stock']], 400);
            }

            $totalPrice = $product->price * $validated['quantity']; // 初始價格

            // 檢查選擇的選項的庫存與價格
            if (isset($validated['options'])) {
                foreach ($validated['options'] as $option) {
                    foreach ($option['option_values'] as $optionValue) {
                        $optionValueModel = ProductOptionValue::find($optionValue['id']);
                        if ($optionValueModel) {
                            // 檢查選項的庫存
                            if ($optionValueModel->enable_stock && $optionValueModel->stock < $validated['quantity']) {
                                return response()->json(['code' => 400, 'data' => ['message' => 'Option "' . $optionValueModel->value . '" out of stock']], 400);
                            }
                            // 計算價格
                            $totalPrice += $optionValue['price'] * $validated['quantity'];
                        }
                    }
                }
            }

            DB::beginTransaction();

            $cartItem = $cart->items()->where('product_id', $validated['product_id'])->first();

            // 更新購物車商品
            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $validated['quantity'];

                if ($product_stock != -999 && $product->stock < $newQuantity) {
                    DB::rollBack();
                    return response()->json(['code' => 400, 'data' => ['message' => 'Add product failed. Out of stock']], 400);
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->price = $totalPrice;
                $cartItem->options = isset($validated['options']) ? json_encode($validated['options']) : null;
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'price' => $totalPrice,
                    'options' => isset($validated['options']) ? json_encode($validated['options']) : null,
                ]);
            }

            // 更新商品庫存
            if ($product_stock != -999) {
                $product->decrement('stock', $validated['quantity']);
            }

            // 更新選項庫存
            if (isset($validated['options'])) {
                foreach ($validated['options'] as $option) {
                    foreach ($option['option_values'] as $optionValue) {
                        $optionValueModel = ProductOptionValue::find($optionValue['id']);
                        if ($optionValueModel && $optionValueModel->enable_stock) {
                            $optionValueModel->decrement('stock', $validated['quantity']);
                        }
                    }
                }
            }

            $cart->calculateTotalPrice();
            DB::commit();

            return response()->json(['code' => 201, 'data' => ['message' => 'Add product into cart successfully']], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($id);
            $product = Product::where('id', $cartItem->product_id)->first();
            $newQuantity = $validated['quantity'];
            $currentQuantity = $cartItem->quantity;

            if ($product->enable_stock && $newQuantity > $currentQuantity) {
                $requiredStock = $newQuantity - $currentQuantity;
                if ($product->stock < $requiredStock) {
                    return response()->json(['code' => 400, 'data' => ['message' => 'Update failed. Not enough stock']], 400);
                }
            }

            if ($product->enable_stock) {
                if ($newQuantity > $currentQuantity) {
                    $product->decrement('stock', $newQuantity - $currentQuantity);
                } elseif ($newQuantity < $currentQuantity) {
                    $product->increment('stock', $currentQuantity - $newQuantity);
                }
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->price = $newQuantity * $product->price;
            $cartItem->save();

            $cartItem->cart->calculateTotalPrice();

            DB::commit();

            return response()->json(['code' => 200, 'data' => ['message' => 'Update cart successfully']], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($id);
            $cart = $cartItem->cart;
            $product = Product::where('id', $cartItem->product_id)->first();

            if ($product->enable_stock) {
                $product->increment('stock', $cartItem->quantity);
            }

            $cartItem->delete();
            $cart->calculateTotalPrice();

            DB::commit();

            return response()->json(['code' => 200, 'data' => ['message' => 'Deleted cart item successfully']], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => ['message' => $e->getMessage()]], 500);
        }
    }

}
