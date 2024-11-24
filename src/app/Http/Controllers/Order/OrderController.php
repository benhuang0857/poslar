<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\ProductOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use DB;

class OrderController extends Controller
{
    public function store(Request $request, $serial_number)
    {
        try {
            $validated = $request->validate([
                'paid' => 'required|boolean',
            ]);

            $cart = Cart::where('serial_number', $serial_number)
                        ->where('status', 'checked_out')
                        ->firstOrFail();

            // 檢查是否已有訂單
            $orderExists = Order::where('serial_number', $cart->serial_number)->exists();

            if ($orderExists) {
                return response()->json([
                    'code' => 400,
                    'data' => 'Order with this serial number already exists.',
                ], 400);
            }

            $options = CartItem::where('cart_id', $cart->id)->pluck('id')->toArray();

            // 建立 Order
            $order = new Order();
            $order->user_id = $cart->user_id;
            $order->serial_number = $cart->serial_number;
            $order->customer_id = $cart->customer_id;
            $order->dining_table_id = $cart->dining_table_id;
            $order->payment_id = $cart->payment_id;
            $order->promotion_id = $cart->promotion_id;
            $order->total_price = $cart->total_price;
            $order->final_price = $cart->final_price;
            $order->paid = $request->paid;
            $order->status = 'process';
            $order->save();

            // 複製 Cart Items 到 Order Items，並包含選項值
            foreach ($cart->items as $cartItem) {

                // 儲存 OrderItem
                $orderItem = $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price
                ]);

                // 儲存 OrderItemProductOptionValues
                $orderItem->options()->attach($options);
            }

            return response()->json([
                'code' => 201,
                'data' => [
                    'message' => 'Create order successfully',
                    'serial_number' => $order->serial_number,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $serial_number) {
        try {
            $validated = $request->validate([
                'paid' => 'required|boolean',
                'status' => 'nullable|string'
            ]);

            $order = Order::where('serial_number', $serial_number)->firstOrFail();
            $order->paid = $request->paid;
            $order->status = isset($request->status) ?? $cart->status ;
            $order->save();

            return response()->json(['code' => http_response_code(), 'data' => [
                'message' => 'Update order successfully',
                'serial_number' => $order->serial_number
            ]], 201);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

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

    public function destroy($id)
    {
        try {
            $order = Order::where('user_id', $id)->first();

            if ($order) {
                $order->items()->delete();
                $order->total_price = 0;
                $order->save();
            }

            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Cart empty']], 204);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }
}
