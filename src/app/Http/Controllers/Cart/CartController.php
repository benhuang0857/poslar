<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Models\Cart\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;


class CartController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|min:1',
                'dining_table_id' => 'nullable|integer|min:1',
            ]);

            $cart = new Cart();
            $cart->serial_number = $cart->generateSerialNumber();
            $cart->user_id = $request->user_id;
            $cart->dining_table_id = isset($request->dining_table_id) ?? null ;
            $cart->save();

            return response()->json(['code' => http_response_code(), 'data' => [
                'message' => 'Add product into cart successfully',
                'serial_number' => $cart->serial_number
            ]], 201);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $serial_number) {}

    public function show($serial_number)
    {
        try {
            $result = Cart::with('items')->where('serial_number', $serial_number)->first();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
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
