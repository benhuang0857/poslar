<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductOptionValue;
use Illuminate\Http\Request;

class ProductOptionValueController extends Controller
{
    public function all()
    {
        try {
            $optionValues = ProductOptionValue::with('optionType')->get();
            return response()->json(['code' => 200, 'data' => ['list' => $optionValues]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_option_type_id' => 'required|exists:product_option_types,id',
                'value' => 'required|string|max:255',
                'image' => 'nullable|string|max:255',
                'enable_stock' => 'nullable|boolean',
                'stock' => 'nullable|integer',
                'enable_price' => 'nullable|boolean',
                'price' => 'nullable|numeric|min:0',
            ]);

            if (!isset($validated['enable_stock']) || !$validated['enable_stock']) {
                $validated['stock'] = -1;
            }

            if (!isset($validated['enable_price']) || !$validated['enable_price']) {
                $validated['price'] = -1;
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('public/images');
                $validated['image'] = $imagePath;
            }  

            $optionValue = ProductOptionValue::create($validated);

            return response()->json(['code' => 201, 'data' => ['message' => 'Success', 'product_option_value' => $optionValue]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $optionValue = ProductOptionValue::with('optionType')->findOrFail($id);
            return response()->json(['code' => 200, 'data' => $optionValue]);
        } catch (\Exception $e) {
            return response()->json(['code' => 404, 'message' => 'Resource not found'], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'product_option_type_id' => 'required|exists:product_option_types,id',
                'value' => 'required|string|max:255',
                'image' => 'nullable|string|max:255',
                'enable_stock' => 'nullable|boolean',
                'stock' => 'nullable|integer',
                'enable_price' => 'nullable|boolean',
                'price' => 'nullable|numeric|min:0',
            ]);

            $optionValue = ProductOptionValue::findOrFail($id);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('public/images');
                $validated['image'] = $imagePath;
            }  
            
            $optionValue->update($validated);
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
                'ids.*' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            ProductOptionValue::whereIn('id', $ids)->delete();
            
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
