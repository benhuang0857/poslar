<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use App\Models\Product\ProductOptionType;
use App\Models\Product\ProductOptionValue;
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

    /** Normal without base64 */ 
    // public function store(Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'enable_adv_sku' => 'nullable|boolean',
    //             'sku' => 'nullable|string|max:255',
    //             'feature_image' => 'nullable|string|max:255',
    //             'price' => 'required|numeric|min:0',
    //             'enable_stock' => 'required|boolean',
    //             'stock' => 'nullable|integer|min:0',
    //             'description' => 'nullable|string',
    //             'status' => 'required|boolean',
    //             'option_types_with_option_value' => 'nullable|array',
    //             'categories' => 'nullable|array',
    //         ]);
    
    //         if ($request->hasFile('feature_image')) {
    //             $image = $request->file('feature_image');
    //             $imagePath = $image->store('public/images'); // Save image in the 'public/images' directory
    //             $validated['feature_image'] = $imagePath; // Store the path in the database
    //         }
    
    //         $product = Product::create($validated);
    
    //         // Process option_types_with_option_value
    //         if (isset($request->option_types_with_option_value)) {
    //             foreach ($request->option_types_with_option_value as $optionTypeWithValues) {
    //                 // Handle image upload for option type
    //                 $optionTypeImagePath = null;
    //                 if (isset($optionTypeWithValues['image']) && $optionTypeWithValues['image'] instanceof \Illuminate\Http\UploadedFile) {
    //                     $optionTypeImagePath = $optionTypeWithValues['image']->store('public/option_types');
    //                 }
    
    //                 // Find or create the option type
    //                 $optionType = ProductOptionType::firstOrCreate([
    //                     'name' => $optionTypeWithValues['option_type_name'],
    //                 ], [
    //                     'image' => $optionTypeImagePath,
    //                     'enable_multi_select' => $optionTypeWithValues['enable_multi_select'] ?? false,
    //                 ]);
    
    //                 // Attach the option type to the product
    //                 $product->optionTypes()->attach($optionType->id);
    
    //                 // Process option values
    //                 if (isset($optionTypeWithValues['option_values'])) {
    //                     foreach ($optionTypeWithValues['option_values'] as $optionValue) {
    //                         // Handle image upload for option value
    //                         $optionValueImagePath = null;
    //                         if (isset($optionValue['image']) && $optionValue['image'] instanceof \Illuminate\Http\UploadedFile) {
    //                             $optionValueImagePath = $optionValue['image']->store('public/option_values');
    //                         }
    
    //                         // Find or create the option value using 'value' as a key
    //                         $optionValueModel = ProductOptionValue::firstOrCreate([
    //                             'product_option_type_id' => $optionType->id,
    //                             'value' => $optionValue['value'],
    //                         ], [
    //                             'enable_stock' => $optionValue['enable_stock'] ?? false,
    //                             'stock' => $optionValue['stock'] ?? -999,
    //                             'enable_price' => $optionValue['enable_price'] ?? false,
    //                             'price' => $optionValue['price'] ?? 0,
    //                             'image' => $optionValueImagePath,
    //                         ]);
    
    //                         // Attach the option value to the product
    //                         $product->optionValues()->attach($optionValueModel->id);
    //                     }
    //                 }
    //             }
    //         }
    
    //         // Attach categories
    //         if (isset($request->categories)) {
    //             $product->categories()->attach($request->categories);
    //         }
    
    //         return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']], 201); // 返回201狀態碼
    //     } catch (Exception $e) {
    //         return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
    //     }
    // }

    // public function update($id, Request $request)
    // {
    //     try {
    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'enable_adv_sku' => 'nullable|boolean',
    //             'sku' => 'nullable|string|max:255',
    //             'feature_image' => 'nullable|string|max:255',
    //             'price' => 'required|numeric|min:0',
    //             'enable_stock' => 'required|boolean',
    //             'stock' => 'nullable|integer|min:0',
    //             'description' => 'nullable|string',
    //             'status' => 'required|boolean',
    //             'option_types_with_option_value' => 'nullable|array',
    //             'categories' => 'nullable|array',
    //         ]);
    
    //         $product = Product::findOrFail($id);
    
    //         // Handle feature image upload
    //         if ($request->hasFile('feature_image')) {
    //             $image = $request->file('feature_image');
    //             $imagePath = $image->store('public/images');
    //             $validated['feature_image'] = $imagePath;
    //         }
    
    //         // Update product details
    //         $product->update($validated);
    
    //         // Update option types and values
    //         if (isset($request->option_types_with_option_value)) {
    //             $optionTypeIds = [];
    //             $optionValueIds = [];
    
    //             foreach ($request->option_types_with_option_value as $optionTypeWithValues) {
    //                 // Handle option type image upload
    //                 $optionTypeImagePath = null;
    //                 if (isset($optionTypeWithValues['image']) && $optionTypeWithValues['image'] instanceof \Illuminate\Http\UploadedFile) {
    //                     $optionTypeImagePath = $optionTypeWithValues['image']->store('public/option_types');
    //                 }
    
    //                 // Find or create option type
    //                 $optionType = ProductOptionType::firstOrCreate([
    //                     'name' => $optionTypeWithValues['option_type_name'],
    //                 ], [
    //                     'image' => $optionTypeImagePath,
    //                     'enable_multi_select' => $optionTypeWithValues['enable_multi_select'] ?? false,
    //                 ]);
    
    //                 $optionTypeIds[] = $optionType->id;
    
    //                 // Handle option values
    //                 if (isset($optionTypeWithValues['option_values'])) {
    //                     foreach ($optionTypeWithValues['option_values'] as $optionValue) {
    //                         // Handle option value image upload
    //                         $optionValueImagePath = null;
    //                         if (isset($optionValue['image']) && $optionValue['image'] instanceof \Illuminate\Http\UploadedFile) {
    //                             $optionValueImagePath = $optionValue['image']->store('public/option_values');
    //                         }
    
    //                         // Find or create option value
    //                         $optionValueModel = ProductOptionValue::firstOrCreate([
    //                             'product_option_type_id' => $optionType->id,
    //                             'value' => $optionValue['value'],
    //                         ], [
    //                             'enable_stock' => $optionValue['enable_stock'] ?? false,
    //                             'stock' => $optionValue['stock'] ?? -999,
    //                             'enable_price' => $optionValue['enable_price'] ?? false,
    //                             'price' => $optionValue['price'] ?? 0,
    //                             'image' => $optionValueImagePath,
    //                         ]);
    
    //                         $optionValueIds[] = $optionValueModel->id;
    //                     }
    //                 }
    //             }
    
    //             // Sync option types and values
    //             $product->optionTypes()->sync($optionTypeIds);
    //             $product->optionValues()->sync($optionValueIds);
    //         }
    
    //         // Update categories
    //         if (isset($request->categories)) {
    //             $product->categories()->sync($request->categories);
    //         }
    
    //         return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']]);
    //     } catch (Exception $e) {
    //         return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
    //     }
    // }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'enable_adv_sku' => 'nullable|boolean',
                'sku' => 'nullable|string|max:255',
                'feature_image' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'enable_stock' => 'required|boolean',
                'stock' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
                'option_types_with_option_value' => 'nullable|array',
                'categories' => 'nullable|array',
            ]);

            // 處理 Base64 Feature Image
            if (isset($validated['feature_image']) && $this->isBase64($validated['feature_image'])) {
                $validated['feature_image'] = $this->saveBase64Image($validated['feature_image'], 'public/images');
            }

            $product = Product::create($validated);

            // Process option_types_with_option_value
            if (isset($request->option_types_with_option_value)) {
                foreach ($request->option_types_with_option_value as $optionTypeWithValues) {
                    // Handle Base64 image for option type
                    $optionTypeImagePath = null;
                    if (isset($optionTypeWithValues['image']) && $this->isBase64($optionTypeWithValues['image'])) {
                        $optionTypeImagePath = $this->saveBase64Image($optionTypeWithValues['image'], 'public/option_types');
                    }

                    // Find or create the option type
                    $optionType = ProductOptionType::firstOrCreate([
                        'name' => $optionTypeWithValues['option_type_name'],
                    ], [
                        'image' => $optionTypeImagePath,
                        'enable_multi_select' => $optionTypeWithValues['enable_multi_select'] ?? false,
                    ]);

                    // Attach the option type to the product
                    $product->optionTypes()->attach($optionType->id);

                    // Process option values
                    if (isset($optionTypeWithValues['option_values'])) {
                        foreach ($optionTypeWithValues['option_values'] as $optionValue) {
                            // Handle Base64 image for option value
                            $optionValueImagePath = null;
                            if (isset($optionValue['image']) && $this->isBase64($optionValue['image'])) {
                                $optionValueImagePath = $this->saveBase64Image($optionValue['image'], 'public/option_values');
                            }

                            // Find or create the option value using 'value' as a key
                            $optionValueModel = ProductOptionValue::firstOrCreate([
                                'product_option_type_id' => $optionType->id,
                                'value' => $optionValue['value'],
                            ], [
                                'enable_stock' => $optionValue['enable_stock'] ?? false,
                                'stock' => $optionValue['stock'] ?? -999,
                                'enable_price' => $optionValue['enable_price'] ?? false,
                                'price' => $optionValue['price'] ?? 0,
                                'image' => $optionValueImagePath,
                            ]);

                            // Attach the option value to the product
                            $product->optionValues()->attach($optionValueModel->id);
                        }
                    }
                }
            }

            // Attach categories
            if (isset($request->categories)) {
                $product->categories()->attach($request->categories);
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
                'feature_image' => 'nullable|string', // 支持 Base64 圖片
                'price' => 'required|numeric|min:0',
                'enable_stock' => 'required|boolean',
                'stock' => 'nullable|integer|min:0',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
                'option_types_with_option_value' => 'nullable|array',
                'categories' => 'nullable|array',
            ]);
    
            $product = Product::findOrFail($id);
    
            // Handle feature image upload (support Base64)
            if (isset($validated['feature_image'])) {
                if ($this->isBase64($validated['feature_image'])) {
                    $validated['feature_image'] = $this->saveBase64Image($validated['feature_image'], 'public/images');
                } elseif ($request->hasFile('feature_image')) {
                    $image = $request->file('feature_image');
                    $validated['feature_image'] = $image->store('public/images');
                }
            }
    
            // Update product details
            $product->update($validated);
    
            // Update option types and values
            if (isset($request->option_types_with_option_value)) {
                $optionTypeIds = [];
                $optionValueIds = [];
    
                foreach ($request->option_types_with_option_value as $optionTypeWithValues) {
                    // Handle option type image upload (support Base64)
                    $optionTypeImagePath = null;
                    if (isset($optionTypeWithValues['image'])) {
                        if ($this->isBase64($optionTypeWithValues['image'])) {
                            $optionTypeImagePath = $this->saveBase64Image($optionTypeWithValues['image'], 'public/option_types');
                        } elseif ($optionTypeWithValues['image'] instanceof \Illuminate\Http\UploadedFile) {
                            $optionTypeImagePath = $optionTypeWithValues['image']->store('public/option_types');
                        }
                    }
    
                    // Find or create option type
                    $optionType = ProductOptionType::firstOrCreate([
                        'name' => $optionTypeWithValues['option_type_name'],
                    ], [
                        'image' => $optionTypeImagePath,
                        'enable_multi_select' => $optionTypeWithValues['enable_multi_select'] ?? false,
                    ]);
    
                    $optionTypeIds[] = $optionType->id;
    
                    // Handle option values
                    if (isset($optionTypeWithValues['option_values'])) {
                        foreach ($optionTypeWithValues['option_values'] as $optionValue) {
                            // Handle option value image upload (support Base64)
                            $optionValueImagePath = null;
                            if (isset($optionValue['image'])) {
                                if ($this->isBase64($optionValue['image'])) {
                                    $optionValueImagePath = $this->saveBase64Image($optionValue['image'], 'public/option_values');
                                } elseif ($optionValue['image'] instanceof \Illuminate\Http\UploadedFile) {
                                    $optionValueImagePath = $optionValue['image']->store('public/option_values');
                                }
                            }
    
                            // Find or update option value
                            $optionValueModel = ProductOptionValue::updateOrCreate(
                                [
                                    'product_option_type_id' => $optionType->id,
                                    'value' => $optionValue['value'],
                                ],
                                [
                                    'enable_stock' => $optionValue['enable_stock'] ?? false,
                                    'stock' => $optionValue['stock'] ?? -999,
                                    'enable_price' => $optionValue['enable_price'] ?? false,
                                    'price' => $optionValue['price'] ?? 0,
                                    'image' => $optionValueImagePath,
                                ]
                            );
    
                            $optionValueIds[] = $optionValueModel->id;
                        }
                    }
                }
    
                // Sync option types and values
                $product->optionTypes()->sync($optionTypeIds);
                $product->optionValues()->sync($optionValueIds);
            }
    
            // Update categories
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
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:products,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $ids = $request->input('ids');
    
            // 查詢並刪除商品及其關聯數據
            $products = Product::whereIn('id', $ids)->get();
    
            foreach ($products as $product) {
                // 刪除與 product_option_types_products 的關聯
                $product->optionTypes()->detach();
                // 刪除商品
                $product->delete();
            }
    
            return response()->json(null, 204); // 成功刪除
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'An error occurred',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function isBase64($string)
    {
        // 檢查是否符合 Base64 圖片的正則表達式格式
        return (bool) preg_match('/^data:image\/[a-zA-Z]+;base64,/', $string);
    }

    private function saveBase64Image($base64Image, $path)
    {
        // 檢查是否為有效的 Base64 編碼
        if (!$this->isBase64($base64Image)) {
            throw new \Exception('Invalid Base64 image format.');
        }

        // 提取圖片格式
        preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches);
        $extension = $matches[1] ?? 'png'; // 默認擴展名為 png

        // 解碼 Base64
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64Image));

        // 確保目錄存在
        $storagePath = storage_path("app/{$path}");
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        // 生成唯一文件名
        $fileName = uniqid() . '.' . $extension;
        $filePath = "{$path}/{$fileName}";

        // 保存圖片
        file_put_contents(storage_path("app/{$filePath}"), $imageData);

        // 返回路徑（相對於 storage/app/public）
        return str_replace('public/', 'storage/', $filePath);
    }

    
}
