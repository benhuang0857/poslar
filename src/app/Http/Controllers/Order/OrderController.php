<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Order\OrderItem;
use App\Models\Cart\Cart;
use App\Models\Cart\CartItem;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionValue;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class OrderController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function all(Request $request)
    {
        try {
            // Initialize the query with necessary relationships
            $query = Order::with([
                'user',
                'customer',
                'payment',
                'promotion',
                'dining_table',
                'items.product',
                'items.options.optionType',
            ]);
    
            // Extract filters from request
            $filters = $request->only(['start_date', 'end_date', 'status', 'paid', 'shipping']);
    
            // Apply filters using the service
            $query = $this->checkoutService->applyFilters($query, $filters);
    
            // Retrieve all results without pagination
            $result = $query->get();
    
            // Return response
            return response()->json([
                'code' => 200,
                'data' => [
                    'list' => $result, // Directly return all records
                ],
            ]);
        } catch (Exception $e) {
            // Handle any unexpected exceptions
            return response()->json([
                'code' => 500,
                'data' => $e->getMessage(),
            ], 500);
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
                'items.options.optionType',
            ])->where('serial_number', $serial_number)->first();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate request data
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

            // Create order
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

                // Check product stock
                $this->checkoutService->checkStock($product, $productData['quantity'], 'product');

                $productOptions = [];
                $totalItemPrice = $product->price; // Base product price

                if (!empty($productData['options'])) {
                    $optionValues = ProductOptionValue::whereIn('id', $productData['options'])->get();

                    foreach ($optionValues as $optionValue) {
                        // Check option stock
                        $this->checkoutService->checkStock($optionValue, $productData['quantity'], 'options');
                        // Add option price to total price if enabled
                        if ($optionValue->enable_price) $totalItemPrice += $optionValue->price;
                        // Decrease option stock if enabled
                        if($optionValue->enable) $optionValue->decrement('stock', $productData['quantity']);
                        // Save option values
                        $productOptions[] = $optionValue->value;
                    }
                }

                // Create order item
                $orderItem = $order->items()->create([
                    'product_id'   => $product->id,
                    'quantity'     => $productData['quantity'],
                    'price'        => $totalItemPrice * $productData['quantity'],
                    'product_name' => $product->name,
                    'product_option' => json_encode($productOptions), // Save option values as JSON
                ]);

                // Attach options to the order item
                if (!empty($productData['options'])) {
                    $orderItem->options()->attach($productData['options']);
                }

                // Decrease product stock
                if ($product->enable_stock) {
                    $product->decrement('stock', $productData['quantity']);
                }
            }

            // Calculate order total and final prices
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
        } catch (OutOfStockException $e) {
            return response()->json([
                'code' => 422,
                'data' => [
                    'message' => $e->getMessage(),
                    'details' => $e->getData(),
                ],
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
                'status' => 'nullable|string|in:process,pending,completed,cancelled,delivered',
            ]);
    
            // Find the order by serial number
            $order = Order::where('serial_number', $serial_number)->firstOrFail();
    
            // Update the order fields
            $order->fill([
                'paid' => $validated['paid'],
                'status' => $validated['status'] ?? $order->status, // Use current status if no new one is provided
            ])->save();
    
            // Return a success response
            return response()->json([
                'code' => 200,
                'data' => [
                    'message' => 'Order updated successfully.',
                    'serial_number' => $order->serial_number,
                ],
            ], 200);
    
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'code' => 500,
                'data' => $e->getMessage(),
            ], 500);
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
            ])
            ->whereDate('created_at', Carbon::today())
            ->get();

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
