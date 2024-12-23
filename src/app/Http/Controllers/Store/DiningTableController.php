<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\DiningTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class DiningTableController extends Controller
{
    public function all()
    {
        try {
            $dining_tables = DiningTable::all();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $dining_tables]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = DiningTable::where('id', $id)->findOrFail($id);
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
                'quantity' => 'nullable|integer|min:0',
                'qrcode' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
            ]);
    
            $dining_table = DiningTable::create($validated);
            
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
                'quantity' => 'nullable|integer|min:0',
                'qrcode' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|boolean',
            ]);

            $dining_table = DiningTable::findOrFail($id);
            $dining_table->update($validated);
            
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
                'ids.*' => 'integer|exists:dining_table,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            DiningTable::whereIn('id', $ids)->delete();

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
