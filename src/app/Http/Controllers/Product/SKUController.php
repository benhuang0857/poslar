<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\SKU;
use App\Services\SkuGeneratorService;
use Illuminate\Http\Request;

class SKUController extends Controller
{
    public function all()
    {
        try {
            $skus = SKU::with('product')->get();
            return response()->json(['code' => 200, 'data' => $skus]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Failed to fetch SKUs', 'error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'sku' => 'nullable|string|max:255',
                'stock' => 'required|integer|min:0',
                'price' => 'required|numeric|min:0',
                'option_values' => 'required|array',
                'option_values.*' => 'exists:product_option_values,id',
            ]);

            $sku = SKU::create($validated);
            $sku->optionValues()->attach($validated['option_values']);

            return response()->json(['code' => 201, 'data' => $sku]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Failed to create SKU', 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $sku = SKU::with('product')->findOrFail($id);
            return response()->json(['code' => 200, 'data' => $sku]);
        } catch (Exception $e) {
            return response()->json(['code' => 404, 'message' => 'SKU not found', 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'sku' => 'nullable|string|max:255',
                'stock' => 'required|integer|min:0',
                'price' => 'required|numeric|min:0',
                'option_values' => 'required|array',
                'option_values.*' => 'exists:product_option_values,id',
            ]);

            $sku = SKU::findOrFail($id);
            $sku->update($validated);

            $sku->optionValues()->sync($validated['option_values']);

            return response()->json(['code' => 200, 'data' => $sku]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Failed to update SKU', 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $sku = SKU::findOrFail($id);
            $sku->delete();
            return response()->json(null, 204);
        } catch (Exception $e) {
            return response()->json(['code' => 404, 'message' => 'SKU not found', 'error' => $e->getMessage()]);
        }
    }

    public function genSKUs($id)
    {
        $skuService = new SkuGeneratorService();
        $skuService->generateProductSkus($id);
    }
}
