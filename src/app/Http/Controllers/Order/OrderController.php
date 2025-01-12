<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\OutOfStockException;
use Exception;
use DB;

class OrderController extends Controller
{
    public function all()
    {
        try {
            $result = Order::with([
                'user',
                'customer',
                'payment',
                'promotion',
                'dining_table',
                'items.product',
                'items.options',
            ])->get();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function show($serial_number)
    {
        try {
            $result = Order::with([
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
            // 驗證請求數據
            $validated = $request->validate([
                'user_id'           => 'required|integer|min:1',
                'customer_id'       => 'nullable|integer|min:1',
                'dining_table_id'   => 'nullable|integer|min:1',
                'payment_id'        => 'nullable|integer|min:1',
                'promotion_id'      => 'nullable|integer|min:1',
                'paid'              => 'required|boolean',
                'shipping'          => 'required|string',
                'note'              => 'nullable|string',
                'products'          => 'required|array',
                'products.*.id'     => 'required|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.options'  => 'array',
                'products.*.options.*' => 'exists:product_option_values,id',
            ]);

            DB::beginTransaction();

            // 創建訂單
            $order = Order::create([
                'serial_number'     => (new Order)->generateSerialNumber(),
                'user_id'           => $validated['user_id'],
                'customer_id'       => $validated['customer_id'] ?? null,
                'dining_table_id'   => $validated['dining_table_id'] ?? null,
                'payment_id'        => $validated['payment_id'] ?? null,
                'promotion_id'      => $validated['promotion_id'] ?? null,
                'total_price'       => 0,
                'final_price'       => 0,
                'paid'              => $validated['paid'],
                'shipping'          => $validated['shipping'],
                'note'              => $validated['note'] ?? null,
            ]);

            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);

                if ($product->enable_stock && $product->stock < $productData['quantity']) {
                    // throw new Exception('Product "' . $product->name. '(' . $product->id . ')' . '" is out of stock');
                    return response()->json([
                        'code' => 201,
                        'data' => [
                            'product_id' => $product->id,
                            'message' => '商品: "' . $product->name. '" 售罄',
                            'serial_number' => $order->serial_number,
                        ],
                    ], 201);
                }

                $productOptions = [];

                if (!empty($productData['options'])) {
                    $optionValues = ProductOptionValue::whereIn('id', $productData['options'])->get();

                    foreach ($optionValues as $optionValue) {
                        if ($optionValue->enable_stock && $optionValue->stock < $productData['quantity']) {
                            // throw new Exception('Option "' . $optionValue->value . '" is out of stock');

                            return response()->json([
                                'code' => 201,
                                'data' => [
                                    'product_id' => $product->id,
                                    'option_value_id' => $optionValue->id,
                                    'message' => '商品品項: "' . $optionValue->value . '" 售罄',
                                    'serial_number' => $order->serial_number,
                                ],
                            ], 201);
                        }

                        // Create order item for each option
                        $orderItem = $order->items()->create([
                            'product_id'   => $product->id,
                            'quantity'     => $productData['quantity'],
                            'price'        => $product->price * $productData['quantity'],
                            'product_name' => $product->name, // Save the product name
                            'product_option' => $optionValue->value
                        ]);

                        // Attach the selected option to the order item
                        $orderItem->options()->attach($optionValue->id);
                        $optionValue->decrement('stock', $productData['quantity']);

                        // Store the option values as a string or JSON
                        $productOptions[] = $optionValue->value;
                    }
                } else {
                    // Create order item without options
                    $orderItem = $order->items()->create([
                        'product_id'   => $product->id,
                        'quantity'     => $productData['quantity'],
                        'price'        => $product->price * $productData['quantity'],
                        'product_name' => $product->name, // Save the product name
                    ]);
                }

                // Update the product's stock if necessary
                if ($product->enable_stock) {
                    $product->decrement('stock', $productData['quantity']);
                }
            }

            // Calculate the total and final price
            $order->calculateTotalPrice();
            $order->calculateFinalPrice();

            DB::commit();

            return response()->json([
                'code' => 200,
                'data' => [
                    'message' => 'Create order successfully',
                    'serial_number' => $order->serial_number,
                ],
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'data' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'data' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $serial_number)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'paid'   => 'required|boolean',
                'status' => 'nullable|string',
            ]);
    
            // Find the order by serial number
            $order = Order::where('serial_number', $serial_number)->firstOrFail();
    
            // Update the order fields
            $order->paid = $validated['paid'];
            $order->status = isset($validated['status']) ? $validated['status'] : $order->status;  // Use the current status if no new one is provided
            $order->save();
    
            // Return a success response
            return response()->json([
                'code' => 200, // HTTP status code for success
                'data' => [
                    'message' => 'Update order successfully',
                    'serial_number' => $order->serial_number,
                ]
            ], 200); // 200 OK
    
        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'code' => 500, // Internal server error
                'data' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:users,id',
            ]);

            $userIds = $validated['ids'];

            $orders = Order::whereIn('user_id', $userIds)->get();
    
            if ($orders->isEmpty()) {
                return response()->json([
                    'code' => 404,
                    'data' => ['message' => 'No orders found for the given user IDs'],
                ], 404);
            }
    
            foreach ($orders as $order) {
                $order->items()->delete();

                $order->total_price = 0;
                $order->save();
            }
    
            return response()->json([
                'code' => 200,
                'data' => ['message' => 'Orders emptied successfully'],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'data' => [
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ],
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'data' => ['message' => 'An error occurred', 'error' => $e->getMessage()],
            ], 500);
        }
    }
    
    public function get_kitch_today_order()
    {
        try {
            $result = Order::with([
                'user',
                'customer',
                'payment',
                'promotion',
                'dining_table',
                'items.product',
                'items.options',
            ])->get();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function update_kitch_order(Request $request, $id) 
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'status' => 'required|string',
            ]);
    
            // Find the order by serial number
            $orderItem = OrderItem::where('id', $id)->firstOrFail();
    
            // Update the order fields
            $orderItem->status = $validated['status'];
            $orderItem->save();
    
            // Return a success response
            return response()->json([
                'code' => 200, // HTTP status code for success
                'data' => [
                    'message' => 'Update order item successfully'
                ]
            ], 200); // 200 OK
    
        } catch (Exception $e) {
            // Return an error response
            return response()->json([
                'code' => 500, // Internal server error
                'data' => $e->getMessage(),
            ], 500); // 500 Internal Server Error
        }
    }
}
