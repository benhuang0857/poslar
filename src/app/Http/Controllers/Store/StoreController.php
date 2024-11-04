<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Store\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class StoreController extends Controller
{
    public function all()
    {
    }

    public function show($id)
    {
        try {
            $result = Store::findOrFail($id);
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
                'description' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'website' => 'nullable|url|max:255',
                'logo_url' => 'nullable|url|max:255',
                'opening_hours' => 'nullable|json',
                'social_links' => 'nullable|json',
                'status' => 'required|boolean',
            ]);
    
            $store = Store::create($validated);

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
                'description' => 'nullable|string',
                'address' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'website' => 'nullable|url|max:255',
                'logo_url' => 'nullable|url|max:255',
                'opening_hours' => 'nullable|json',
                'social_links' => 'nullable|json',
                'status' => 'required|boolean',
            ]);

            $store = Store::findOrFail($id);
            $store->update($validated);
            
            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }
}
