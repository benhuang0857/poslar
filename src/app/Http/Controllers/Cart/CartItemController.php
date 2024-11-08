<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
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
                'price' => 'required|numeric|min:0',
                'options' => 'nullable|json',
            ]);
    
            $cart = Cart::where('serial_number', $serial_number)->first();
            if (!$cart) {
                return response()->json(['code' => 404, 'data' => ['message' => 'Cart not found']], 404);
            }
    
            $product = Product::where('id', $validated['product_id'])->first();
            $product_stock = $product->enable_stock ? $product->stock : -999;

            if ($product_stock != -999 && $product->stock < $validated['quantity']) {
                return response()->json(['code' => 400, 'data' => ['message' => 'Add product failed. Out of stock']], 400);
            }
    
            DB::beginTransaction();
    
            $cartItem = $cart->items()->where('product_id', $validated['product_id'])->first();
    
            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $validated['quantity'];

                if ($product_stock != -999 && $product->stock < $newQuantity) {
                    DB::rollBack();
                    return response()->json(['code' => 400, 'data' => ['message' => 'Add product failed. Out of stock']], 400);
                }
    
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'price' => $validated['price'],
                    'options' => $validated['options'] ?? null,
                ]);
            }

            if ($product_stock != -999) {
                $product->decrement('stock', $validated['quantity']);
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
            $product = $cartItem->product;
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
