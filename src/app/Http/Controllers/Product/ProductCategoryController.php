<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductCategoryController extends Controller
{
    public function all()
    {
        try {
            $result = ProductCategory::with([
                'products.optionTypes.optionValues' => function ($query) {
                    $query->whereHas('products'); // 如果需要條件過濾可以在這裡加條件
                }
            ])->get();
    
            return response()->json(['code' => 200, 'data' => ['list' => $result]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }
    

    public function show(string $id)
    {
        try {
            $productCategory = ProductCategory::with([
                'products.optionTypes.optionValues' // 加入嵌套關聯
            ])->findOrFail($id);
    
            return response()->json(['code' => 200, 'data' => $productCategory]);
        } catch (\Exception $e) {
            return response()->json(['code' => 404, 'message' => 'Resource not found'], 404);
        }
    }
    

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255',
                'image' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'products' => 'nullable|array',
            ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('public/images');
                $validated['image'] = $imagePath;
            }   

            $productCategory = ProductCategory::create($validated);

            if (isset($request->products)) {
                $productCategory->products()->attach($request->products);
            }

            return response()->json(['code' => 201, 'data' => ['message' => 'Success', 'product_category' => $productCategory]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255',
                'image' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'products' => 'nullable|array',
            ]);

            $productCategory = ProductCategory::findOrFail($id);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('public/images');
                $validated['image'] = $imagePath;
            }  
            
            $productCategory->update($validated);

            if (isset($request->products)) {
                $productCategory->products()->sync($request->products);
            }

            return response()->json(['code' => 200, 'data' => ['message' => 'Success']]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:dining_tables,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            ProductCategory::whereIn('id', $ids)->delete();
            
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
