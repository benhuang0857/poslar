<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PromotionController extends Controller
{
    public function all()
    {
        try {
            $promotion = Promotion::all();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $promotion]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $promotion = Promotion::where('id', $id)->findOrFail($id);
            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'description'   => 'nullable|string',
                'type'          => 'nullable|string',
                'discount'      => 'nullable|numeric|min:0',
                'enable_expired'=> 'required|boolean',
                'start_time'    => 'nullable|string',
                'end_time'      => 'nullable|string',
                'status'        => 'required|boolean',
            ]);
    
            $promotion = Promotion::create($validated);
            
            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']], 201); // 返回201狀態碼
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'description'   => 'nullable|string',
                'type'          => 'nullable|string',
                'discount'      => 'nullable|numeric|min:0',
                'enable_expired'=> 'required|boolean',
                'start_time'    => 'nullable|string',
                'end_time'      => 'nullable|string',
                'status'        => 'required|boolean',
            ]);

            $promotion = Promotion::findOrFail($id);
            $promotion->update($validated);
            
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
                'ids.*' => 'integer|exists:promotion,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            Promotion::whereIn('id', $ids)->delete();

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
