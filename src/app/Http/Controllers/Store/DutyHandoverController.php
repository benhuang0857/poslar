<?php

namespace App\Http\Controllers\Store;

use App\Models\Store\DutyHandover;
use App\Models\Order\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class DutyHandoverController extends Controller
{
    public function all()
    {
        try {
            $handoverRecords = DutyHandover::with(['user'])->get();

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $handoverRecords]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $result = DutyHandover::with([
                'user'
            ])->findOrFail($id);

            return response()->json(['code' => http_response_code(), 'data' => ['list' => $result]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'note' => 'nullable|string',
            ]);
    
            $handover = DutyHandover::create($validated);
            
            return response()->json(['code' => http_response_code(), 'data' => ['message' => 'Success']], 201);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'sometimes|exists:users,id',
                'note' => 'nullable|string',
                'status' => 'boolean',
            ]);

            $handover = DutyHandover::findOrFail($id);
            $handover->update($validated);
            
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
                'ids.*' => 'integer|exists:duty_handovers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            DutyHandover::whereIn('id', $ids)->delete();

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
