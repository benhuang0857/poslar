<?php

namespace App\Http\Controllers\Store;

use App\Models\Store\DutyShift;
use App\Models\Order\Order;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class DutyShiftController extends Controller
{
    /**
     * Retrieve all duty shifts.
     */
    public function all()
    {
        try {
            $dutyShifts = DutyShift::all();

            return response()->json(['code' => 200, 'data' => ['list' => $dutyShifts]]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retrieve a specific duty shift by ID.
     */
    public function show($id)
    {
        try {
            $dutyShift = DutyShift::findOrFail($id);

            return response()->json(['code' => 200, 'data' => $dutyShift]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new duty shift.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'status' => 'boolean',
            ]);

            $dutyShift = DutyShift::create($validated);

            return response()->json(['code' => 201, 'message' => 'Duty shift created successfully', 'data' => $dutyShift], 201);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing duty shift.
     */
    public function update($id, Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i|after:start_time',
                'status' => 'boolean',
            ]);

            $dutyShift = DutyShift::findOrFail($id);
            $dutyShift->update($validated);

            return response()->json(['code' => 200, 'message' => 'Duty shift updated successfully', 'data' => $dutyShift]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete one or more duty shifts.
     */
    public function destroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:duty_shifts,id',
            ]);

            DutyShift::whereIn('id', $validated['ids'])->delete();

            return response()->json(['code' => 204, 'message' => 'Duty shifts deleted successfully'], 204);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => $e->getMessage()], 500);
        }
    }
}
