<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Product\ProductOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'serial_number' => 'required|integer|min:1',
                'user_id' => 'nullable|integer|min:1',
                'customer_id' => 'nullable|integer|min:1',
                'dining_table_id' => 'nullable|integer|min:1',
                'payment_id' => 'nullable|integer|min:1',
                'promotion_id' => 'nullable|integer|min:1',
                'paid' => 'required|boolean',
                'items.*' => 'exists:product_option_values,id'
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

    public function checkout(Request $request, $serial_number)
    {
        try {
            $validated = $request->validate([
                'payment' => 'required|integer|min:1',
                'promotion' => 'nullable|integer|min:1',
                'status' => 'required|integer|min:1'
            ]);

            $cart = Cart::where('serial_number', $serial_number)->firstOrFail();
            $cart->payment_id = $request->payment;
            $cart->promotion_id = isset($request->promotion) ?? $cart->promotion;
            $cart->status = $request->status;

            if($cart->promotion_id) {
                $promotion = Promotion::where('id', $cart->promotion_id)->firstOrFail();
                $cart->total_price = $cart->total_price * $promotion->discount;
            }

            $cart->save();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $cart]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $cart = Cart::where('user_id', $id)->first();

            if ($cart) {
                $cart->items()->delete();
                $cart->total_price = 0;
                $cart->save();
            }

            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Cart empty']], 204);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }
}
