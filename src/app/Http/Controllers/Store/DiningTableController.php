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
                'quantity' => 'nullable|interger|min:0',
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
                'quantity' => 'nullable|interger|min:0',
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
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer'
            ]);

            $ids = $request->input('ids');
            DiningTable::whereIn('id', $ids)->delete();
            
            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']], 204); // 返回204狀態碼
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }
}
