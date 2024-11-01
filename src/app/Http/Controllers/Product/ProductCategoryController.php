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
            $result = ProductCategory::with('products')->get();
            return response()->json(['code' => 200, 'data' => ['list' => $result]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $productCategory = ProductCategory::with('products')->findOrFail($id);
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
            $productCategory->update($validated);

            if (isset($request->products)) {
                $productCategory->products()->sync($request->products);
            }

            return response()->json(['code' => 200, 'data' => ['message' => 'Success']]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $productCategory = ProductCategory::findOrFail($id);
            $productCategory->delete();

            return response()->json(['code' => 204, 'message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['code' => 404, 'message' => 'Resource not found'], 404);
        }
    }
}
