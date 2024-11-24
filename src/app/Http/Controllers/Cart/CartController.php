<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionValue;
use App\Models\Cart\Cart;
use App\Models\Store\Promotion;
use Illuminate\Support\Facades\Validator;
use Exception;
use DB;

class CartController extends Controller
{
    public function show($serial_number)
    {
        try {
            $result = Cart::with([
                'user',
                'customer',
                'payment',
                'promotion',
                'dining_table',
                'items.product',
                'items.options',
            ])->where('serial_number', $serial_number)->first();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|min:1',
                'customer_id' => 'nullable|integer|min:1',
                'dining_table_id' => 'nullable|integer|min:1',
            ]);

            $cart = new Cart();
            $cart->serial_number = $cart->generateSerialNumber();
            $cart->user_id = $request->user_id;
            $cart->customer_id = isset($request->customer_id) ?? null ;
            $cart->dining_table_id = isset($request->dining_table_id) ?? null ;
            $cart->save();

            return response()->json(['code' => http_response_code(), 'data' => [
                'message' => 'Create cart successfully',
                'serial_number' => $cart->serial_number
            ]], 201);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $serial_number) {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|min:1',
                'customer_id' => 'nullable|integer|min:1',
                'dining_table_id' => 'nullable|integer|min:1',
            ]);

            $cart = Cart::where('serial_number', $serial_number)->firstOrFail();
            $cart->user_id = $request->user_id;
            $cart->customer_id = isset($request->customer_id) ?? $cart->customer_id ;
            $cart->dining_table_id = isset($request->dining_table_id) ?? $cart->dining_table_id ;
            $cart->save();

            return response()->json(['code' => http_response_code(), 'data' => [
                'message' => 'Update cart successfully',
                'serial_number' => $cart->serial_number
            ]], 201);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function checkout(Request $request, $serial_number)
    {
        try {
            $validated = $request->validate([
                'payment' => 'required|integer|min:1',
                'promotion' => 'nullable|integer|min:1'
            ]);

            $cart = Cart::where('serial_number', $serial_number)
                        ->where('status', 'open')
                        ->firstOrFail();
            $cart->payment_id = $request->payment;
            $cart->promotion_id = isset($request->promotion) ?? $cart->promotion;
            $cart->status = 'checked_out';

            if($cart->promotion_id) {
                $promotion = Promotion::where('id', $cart->promotion_id)->firstOrFail();
                $cart->final_price = $cart->total_price * $promotion->discount;
            }

            $cart->save();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $cart]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($serial_number)
    {
        try {
            // 尋找開啟或已結帳的購物車
            $cart = Cart::where('serial_number', $serial_number)
                        ->where(function ($query) {
                            $query->where('status', 'open')
                                ->orWhere('status', 'checked_out');
                        })
                        ->firstOrFail();

            DB::beginTransaction();

            // 處理每個購物車商品項目
            foreach ($cart->items as $item) {
                $product = Product::find($item->product_id);

                if ($product && $product->enable_stock) {
                    // 將商品的庫存退回
                    $product->increment('stock', $item->quantity);
                }

                // 處理商品選項的庫存退回
                foreach ($item->options as $option) {
                    $optionValue = ProductOptionValue::find($option->id);

                    if ($optionValue && $optionValue->enable_stock) {
                        $optionValue->increment('stock', $item->quantity);
                    }
                }
            }

            // 刪除所有購物車項目
            $cart->items()->delete();

            // 更新購物車狀態與總價
            $cart->total_price = 0;
            $cart->status = 'cancelled';
            $cart->save();

            DB::commit();

            return response()->json(['code' => 204, 'data' => ['message' => 'Cart has been cancelled and stock restored']], 204);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'data' => $e->getMessage()], 500);
        }
    }

}
