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
                'options.*' => 'exists:product_option_values,id', // 驗證 options 必須是有效的 ID
            ]);

            $cart = Cart::where('serial_number', $serial_number)->first();
            if (!$cart) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart not found']], 404);
            }

            $product = Product::where('id', $validated['product_id'])->first();
            if ($product->enable_stock && $product->stock < $validated['quantity']) {
                return response()->json(['code' => 400, 'data' => ['message' => 'Add product failed. Out of stock']], 400);
            }

            $totalPrice = $product->price * $validated['quantity']; // 初始價格
            $options = [];
            if (isset($validated['options'])) {
                $optionValues = ProductOptionValue::whereIn('id', $validated['options'])->get();

                foreach ($optionValues as $optionValueModel) {
                    // 檢查選項的庫存
                    if ($optionValueModel->enable_stock && $optionValueModel->stock < $validated['quantity']) {
                        return response()->json([
                            'code' => 400,
                            'data' => ['message' => 'Option "' . $optionValueModel->value . '" out of stock'],
                        ], 400);
                    }

                    // 計算價格
                    $totalPrice += $optionValueModel->price * $validated['quantity'];

                    // 儲存選項 ID
                    $options[] = $optionValueModel->id;
                }
            }

            DB::beginTransaction();

            $cartItem = $cart->items()->where('product_id', $validated['product_id'])->first();

            // 更新購物車商品
            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $validated['quantity'];

                // 檢查產品是否有足夠的庫存
                if ($product->enable_stock && $product->stock < $newQuantity) {
                    DB::rollBack();
                    return response()->json(['code' => 400, 'data' => ['message' => 'Add product failed. Out of stock']], 400);
                }

                // 累積商品價格
                $newTotalPrice = $product->price * $newQuantity;

                // 加上選項價格（如果有）
                if (isset($validated['options'])) {
                    foreach ($optionValues as $optionValueModel) {
                        $newTotalPrice += $optionValueModel->price * $newQuantity;
                    }
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->price = $newTotalPrice; // 設定累積價格
                $cartItem->save();

                // 更新選項關聯
                $cartItem->options()->sync($options);
            } else {
                // 新增新的購物車商品邏輯保持不變
                $cartItem = $cart->items()->create([
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'price' => $totalPrice, // 首次加入時的價格
                ]);

                // 建立選項關聯
                $cartItem->options()->attach($options);
            }

            // 更新商品庫存
            if ($product->enable_stock) {
                $product->decrement('stock', $validated['quantity']);
            }

            // 更新選項庫存
            foreach ($options as $option) {
                $optionValueModel = ProductOptionValue::find($option);
                if ($optionValueModel && $optionValueModel->enable_stock) {
                    $optionValueModel->decrement('stock', $validated['quantity']);
                }
            }

            $cart->calculateTotalPrice();
            $cart->calculateFinalPrice();
            DB::commit();

            return response()->json(['code' => 201, 'data' => ['message' => 'Add product into cart successfully']], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $serial_number, $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
                'options' => 'nullable|array',  // 接收簡化的 options 格式
                'options.*' => 'exists:product_option_values,id',  // 驗證 options 必須是有效的 ID
            ]);

            $cart = Cart::where('serial_number', $serial_number)
                        ->where('status', 'open')
                        ->first();
            if (!$cart) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart not found']], 404);
            }

            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($id);
            $product = Product::where('id', $cartItem->product_id)->first();
            $newQuantity = $validated['quantity'];
            $currentQuantity = $cartItem->quantity;

            // 檢查主商品庫存
            if ($product->enable_stock && $newQuantity > $currentQuantity) {
                $requiredStock = $newQuantity - $currentQuantity;
                if ($product->stock < $requiredStock) {
                    return response()->json(['code' => 400, 'data' => ['message' => 'Update failed. Not enough stock']], 400);
                }
            }

            // 處理選項庫存和價格
            $totalPrice = $product->price * $newQuantity;  // 初始價格

            if (isset($validated['options'])) {
                $optionValues = ProductOptionValue::whereIn('id', $validated['options'])->get();
                
                foreach ($optionValues as $optionValueModel) {
                    // 檢查選項的庫存
                    if ($optionValueModel->enable_stock && $optionValueModel->stock < $newQuantity) {
                        return response()->json([
                            'code' => 400,
                            'data' => ['message' => 'Option "' . $optionValueModel->value . '" out of stock'],
                        ], 400);
                    }

                    // 計算選項價格
                    $totalPrice += $optionValueModel->price * $newQuantity;
                }
            }

            // 更新商品庫存
            if ($product->enable_stock) {
                if ($newQuantity > $currentQuantity) {
                    $product->decrement('stock', $newQuantity - $currentQuantity);
                } elseif ($newQuantity < $currentQuantity) {
                    $product->increment('stock', $currentQuantity - $newQuantity);
                }
            }

            // 更新購物車商品
            $cartItem->quantity = $newQuantity;
            $cartItem->price = $totalPrice;
            $cartItem->save();

            // 更新購物車總價
            $cartItem->cart->calculateTotalPrice();
            $cartItem->cart->calculateFinalPrice();
            DB::commit();

            return response()->json(['code' => 200, 'data' => ['message' => 'Update cart successfully']], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($serial_number, $id)
    {
        try {
            $cart = Cart::where('serial_number', $serial_number)
                        ->where('status', 'open')
                        ->first();
            if (!$cart) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart not found']], 404);
            }
    
            // 查找指定的 cart item，並檢查是否屬於該購物車
            $cartItem = $cart->items()->where('id', $id)->first();
            if (!$cartItem) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart item not found']], 404);
            }
    
            DB::beginTransaction();
    
            $product = $cartItem->product;
    
            // 回補商品庫存
            if ($product->enable_stock) {
                $product->increment('stock', $cartItem->quantity);
            }
    
            // 回補選項庫存
            foreach ($cartItem->options as $optionValue) {
                if ($optionValue->enable_stock) {
                    $optionValue->increment('stock', $cartItem->quantity);
                }
            }
    
            // 刪除購物車項目
            $cartItem->delete();
    
            // 重新計算價格
            $cart->calculateTotalPrice();
            $cart->calculateFinalPrice();
    
            DB::commit();
    
            return response()->json(['code' => 200, 'data' => ['message' => 'Deleted cart item successfully']], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => ['message' => $e->getMessage()]], 500);
        }
    }
    

}