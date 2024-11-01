<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductController extends Controller
{
    public function all()
    {
        try {
            $result = Product::all();
            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = Product::with([
                'optionTypes' => function ($query) use ($id) {
                    $query->with(['optionValues' => function ($query) use ($id) {
                        $query->whereHas('products', function ($query) use ($id) {
                            $query->where('product_id', $id);
                        });
                    }]);
                },
                'skus', 
                'categories'
            ])->findOrFail($id);
            // $result = Product::with(['optionTypes.optionValues', 'skus', 'categories'])->findOrFail($id);
            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'enable_adv_sku' => 'nullable|boolean',
                'sku' => 'nullable|string|max:255',
                'feature_image' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'enable_stock' => 'required|boolean',
                'stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
                'option_types' => 'nullable|array',
                'option_values' => 'nullable|array',
            ]);
    
            $product = Product::create($validated);

            if (isset($request->option_types)) {
                $product->optionTypes()->attach($request->option_types);
            }

            if (isset($request->option_values)) {
                foreach ($request->option_values as $optionValueId) {
                    $product->optionValues()->attach($optionValueId);
                }
            }

            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']], 201); // 返回201狀態碼
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'enable_adv_sku' => 'nullable|boolean',
                'sku' => 'nullable|string|max:255',
                'feature_image' => 'nullable|string|max:255',
                'price' => 'required|numeric|min:0',
                'enable_stock' => 'required|boolean',
                'stock' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
                'option_types' => 'nullable|array',
                'option_values' => 'nullable|array',
            ]);

            $product = Product::findOrFail($id);
            $product->update($validated);

            if (isset($request->option_types)) {
                $product->optionTypes()->sync($request->option_types);
            }

            if (isset($request->option_values)) {
                $product->optionValues()->sync($request->option_values);
            }
            
            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer'
            ]);

            $ids = $request->input('ids');
            Product::whereIn('id', $ids)->delete();
            
            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']], 204); // 返回204狀態碼
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }
}
