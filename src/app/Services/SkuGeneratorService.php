<?php

namespace App\Services;

use App\Models\Product\Product;
use App\Models\Product\Sku;
use App\Models\Product\ProductOptionType;
use App\Models\Product\ProductOptionValue;

class SkuGeneratorService
{
    public function generateProductSkus($productId)
    {
        $product = Product::findOrFail($productId);

        $optionTypes = ProductOptionType::with('optionValues')
                        ->whereHas('products', function ($query) use ($productId) {
                            $query->where('product_id', $productId);
                        })->get();

        if ($optionTypes->isEmpty()) {
            throw new \Exception('No option types available for this product.');
        }

        $optionCombinations = $this->generateOptionCombinations($optionTypes);

        foreach ($optionCombinations as $combination) {
            $this->createSkuForCombination($product, $combination);
        }
    }

    private function generateOptionCombinations($optionTypes)
    {
        $optionValues = [];
        foreach ($optionTypes as $type) {
            $optionValues[] = $type->optionValues->pluck('id')->toArray();
        }

        $combinations = $this->cartesianProduct($optionValues);

        return $combinations;
    }

    private function cartesianProduct($sets)
    {
        $result = [[]];
        foreach ($sets as $set) {
            $newResult = [];
            foreach ($result as $product) {
                foreach ($set as $item) {
                    $newResult[] = array_merge($product, [$item]);
                }
            }
            $result = $newResult;
        }

        return $result;
    }

    private function createSkuForCombination(Product $product, array $combination)
    {
        $skuCode = $product->name . '-' . implode('-', $combination);

        $existingSku = Sku::where('product_id', $product->id)->where('sku', $skuCode)->first();
        if (!$existingSku) {
            $sku = Sku::create([
                'product_id' => $product->id,
                'sku' => $skuCode,
                'stock' => 100,
                'price' => $product->price,
            ]);

            foreach ($combination as $optionValueId) {
                $sku->optionValues()->attach($optionValueId);
            }
        }
    }
}
