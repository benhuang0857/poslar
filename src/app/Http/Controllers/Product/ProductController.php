<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductController extends Controller
{
    public function all()
    {
        try {
            $products = Product::with(['categories'])->get();

            // Lazy Eager Load 相關的 optionTypes 和 optionValues
            $products->each(function ($product) {
                $product->load(['optionTypes' => function ($query) use ($product) {
                    $query->with(['optionValues' => function ($query) use ($product) {
                        $query->whereHas('products', function ($query) use ($product) {
                            $query->where('product_id', $product->id);
                        });
                    }]);
                }]);
            });

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $products]]);
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
                'categories'
            ])->findOrFail($id);
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
                'stock' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
                'option_types' => 'nullable|array',
                'option_values' => 'nullable|array',
                'categories' => 'nullable|array',
            ]);

            if ($request->hasFile('feature_image')) {
                $image = $request->file('feature_image');
                $imagePath = $image->store('public/images'); // Save image in the 'public/images' directory
                $validated['feature_image'] = $imagePath; // Store the path in the database
            }    
    
            $product = Product::create($validated);

            if (isset($request->option_types)) {
                $product->optionTypes()->attach($request->option_types);
            }

            if (isset($request->option_values) && isset($request->option_values)) {
                foreach ($request->option_values as $optionValueId) {
                    $product->optionValues()->attach($optionValueId);
                }
            }

            if(isset($request->option_types) && !isset($request->option_values)) {
                $optionValueIds = ProductOptionType::whereIn('id', $request->option_types)
                                        ->with('optionValues')
                                        ->get()
                                        ->pluck('optionValues.*.id')
                                        ->flatten()
                                        ->toArray();
                foreach ($optionValueIds as $id) {
                    $product->optionValues()->attach($id);
                }
            } 

            if (isset($request->categories)) {
                foreach ($request->categories as $id) {
                    $product->categories()->attach($id);
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
                'stock' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
                'option_types' => 'nullable|array',
                'option_values' => 'nullable|array',
                'categories' => 'nullable|array',
            ]);

            $product = Product::findOrFail($id);

            if ($request->hasFile('feature_image')) {
                $image = $request->file('feature_image');
                $imagePath = $image->store('public/images');
                $validated['feature_image'] = $imagePath;
            }

            $product->update($validated);

            if (isset($request->option_types)) {
                $product->optionTypes()->sync($request->option_types);
            }

            if (isset($request->option_types) && isset($request->option_values)) {
                $product->optionValues()->sync($request->option_values);
            }

            if(isset($request->option_types) && !isset($request->option_values)) {
                $optionValueIds = ProductOptionType::whereIn('id', $request->option_types)
                                        ->with('optionValues')
                                        ->get()
                                        ->pluck('optionValues.*.id')
                                        ->flatten()
                                        ->toArray();
                $product->optionValues()->sync($optionValueIds);
            } 

            if (isset($request->categories)) {
                $product->categories()->sync($request->categories);
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
