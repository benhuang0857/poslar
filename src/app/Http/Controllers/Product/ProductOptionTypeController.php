<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\ProductOptionType;
use Illuminate\Http\Request;

class ProductOptionTypeController extends Controller
{
    public function all()
    {
        try {
            $result = ProductOptionType::with('optionValues')->get();
            return response()->json(['code' => 200, 'data' => ['list' => $result]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'enable_multi_select' => 'nullable|boolean',
            ]);

            $productOptionType = ProductOptionType::create($validated);
            return response()->json(['code' => 201, 'data' => ['message' => 'Success', 'product_option_type' => $productOptionType]]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        try {
            $productOptionType = ProductOptionType::findOrFail($id);
            return response()->json(['code' => 200, 'data' => $productOptionType]);
        } catch (\Exception $e) {
            return response()->json(['code' => 404, 'message' => 'Resource not found'], 404);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'enable_multi_select' => 'nullable|boolean',
            ]);

            $productOptionType = ProductOptionType::findOrFail($id);
            $productOptionType->update($validated);

            return response()->json(['code' => 200, 'data' => ['message' => 'Success']]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $productOptionType = ProductOptionType::findOrFail($id);
            $productOptionType->delete();

            return response()->json(['code' => 204, 'message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['code' => 404, 'message' => 'Resource not found'], 404);
        }
    }
}