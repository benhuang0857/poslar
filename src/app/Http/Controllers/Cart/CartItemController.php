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
    // 提取庫存檢查方法
    private function checkStock($model, $id, $quantity)
    {
        return $model::where('id', $id)
                    ->where('enable_stock', true)
                    ->where('stock', '<', $quantity)
                    ->first();
    }

    // 提取錯誤處理邏輯
    private function handleOutOfStockError($message)
    {
        throw new Exception($message);
    }

    // 新增商品至購物車
    public function store(Request $request, $serial_number)
    {
        try {
            // 驗證輸入
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'options.*' => 'exists:product_option_values,id', // 驗證選項 ID
            ]);

            // 檢查購物車是否存在
            $cart = Cart::where('serial_number', $serial_number)->first();
            if (!$cart) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart not found']], 404);
            }

            // 檢查商品庫存
            $product = Product::find($validated['product_id']);
            if ($this->checkStock(Product::class, $validated['product_id'], $validated['quantity'])) {
                return $this->handleOutOfStockError('Product out of stock');
            }

            $totalPrice = $product->price * $validated['quantity']; // 計算基本價格
            $options = $this->handleOptions($validated['options'], $validated['quantity'], $totalPrice);

            DB::beginTransaction();

            // 更新購物車商品或創建新項目
            $cartItem = $this->updateOrCreateCartItem($cart, $validated['product_id'], $validated['quantity'], $totalPrice, $options);

            // 更新庫存
            $this->updateProductStock($product, $validated['quantity']);
            $this->updateOptionStock($options, $validated['quantity']);

            $cart->calculateTotalPrice();
            DB::commit();

            return response()->json(['code' => 201, 'data' => ['message' => 'Product added to cart successfully']], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => ['message' => $e->getMessage()]], 500);
        }
    }

    // 處理選項邏輯
    private function handleOptions($optionIds, $quantity, &$totalPrice)
    {
        $options = [];
        if (isset($optionIds)) {
            $optionValues = ProductOptionValue::whereIn('id', $optionIds)->get();
            foreach ($optionValues as $optionValueModel) {
                // 檢查選項庫存
                if ($this->checkStock(ProductOptionValue::class, $optionValueModel->id, $quantity)) {
                    return $this->handleOutOfStockError('Option "' . $optionValueModel->value . '" out of stock');
                }

                // 計算價格
                $totalPrice += $optionValueModel->price * $quantity;
                $options[] = $optionValueModel->id;
            }
        }
        return $options;
    }

    // 更新或創建購物車項目
    private function updateOrCreateCartItem($cart, $productId, $quantity, $totalPrice, $options)
    {
        $cartItem = $cart->items()->where('product_id', $productId)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            // 庫存檢查
            if ($this->checkStock(Product::class, $productId, $newQuantity)) {
                DB::rollBack();
                return $this->handleOutOfStockError('Product out of stock');
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->price = $totalPrice;
            $cartItem->save();
            $cartItem->options()->sync($options); // 更新選項
        } else {
            $cartItem = $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $totalPrice,
            ]);
            $cartItem->options()->attach($options); // 創建選項關聯
        }

        return $cartItem;
    }

    // 更新商品庫存
    private function updateProductStock($product, $quantity)
    {
        if ($product->enable_stock) {
            $product->decrement('stock', $quantity);
        }
    }

    // 更新選項庫存
    private function updateOptionStock($options, $quantity)
    {
        foreach ($options as $option) {
            $optionValueModel = ProductOptionValue::find($option);
            if ($optionValueModel && $optionValueModel->enable_stock) {
                $optionValueModel->decrement('stock', $quantity);
            }
        }
    }

    // 更新購物車商品
    public function update(Request $request, $serial_number, $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
                'options' => 'nullable|array',
                'options.*' => 'exists:product_option_values,id',
            ]);
    
            DB::beginTransaction();
            $cartItem = CartItem::findOrFail($id);
            $product = Product::findOrFail($cartItem->product_id);
            $newQuantity = $validated['quantity'];
            $currentQuantity = $cartItem->quantity;
    
            // 檢查商品庫存
            if ($newQuantity > $currentQuantity) {
                if ($this->checkStock(Product::class, $cartItem->product_id, $newQuantity - $currentQuantity)) {
                    return $this->handleOutOfStockError('Not enough product stock');
                }
            }
    
            // 計算總價格及處理選項庫存
            $totalPrice = $product->price * $newQuantity;
            $options = $this->handleOptions($validated['options'], $newQuantity, $totalPrice);
    
            // 更新庫存
            $this->updateProductStock($product, $currentQuantity, $newQuantity);
    
            // 更新購物車商品
            $cartItem->quantity = $newQuantity;
            $cartItem->price = $totalPrice;
            $cartItem->save();
    
            // 更新購物車總價
            $cartItem->cart->calculateTotalPrice();
    
            DB::commit();
    
            return response()->json(['code' => 200, 'data' => ['message' => 'Cart updated successfully']], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => ['message' => $e->getMessage()]], 500);
        }
    }

    // 刪除購物車商品
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
    
            $cartItem = CartItem::findOrFail($id);
            $cart = $cartItem->cart;
            $product = Product::findOrFail($cartItem->product_id);
    
            // 更新商品庫存
            if ($product->enable_stock) {
                $product->increment('stock', $cartItem->quantity);
            }
    
            // 刪除購物車商品
            $cartItem->delete();
            $cart->calculateTotalPrice();
    
            DB::commit();
    
            return response()->json(['code' => 200, 'data' => ['message' => 'Cart item deleted successfully']], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => ['message' => $e->getMessage()]], 500);
        }
    }

}
