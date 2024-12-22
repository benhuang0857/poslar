<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;

class CustomerController extends Controller
{
    public function all()
    {
        try {
            $customers = Customer::all();
            return response()->json(['code' => 200, 'data' => $customers]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Failed to fetch customers', 'error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'      => 'required|string|max:255',
                'mobile'    => 'required|string|max:255|unique:customers',
                'email'     => 'nullable|string|email|max:255',
                'password'  => 'required|string|min:8',
                'line_id'   => 'nullable|string|max:255',
                'birthday'  => 'nullable|date',
            ]);

            $customer = Customer::create([
                'name'      => $request->name,
                'mobile'    => $request->mobile,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'line_id'   => $request->line_id,
                'birthday'  => $request->birthday,
            ]);

            return response()->json(['code' => 201, 'data' => $customer]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Failed to create customer', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            return response()->json(['code' => 200, 'data' => $customer]);
        } catch (Exception $e) {
            return response()->json(['code' => 404, 'message' => 'Customer not found', 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name'      => 'nullable|string|max:255',
                'mobile'    => 'nullable|string|max:255|unique:customers,mobile,' . $id,
                'email'     => 'nullable|string|email|max:255',
                'password'  => 'nullable|string|min:8',
                'line_id'   => 'nullable|string|max:255',
                'birthday'  => 'nullable|date',
            ]);

            $customer = Customer::findOrFail($id);
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
            $customer->update($validated);

            return response()->json(['code' => 200, 'data' => $customer]);
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'message' => 'Failed to update customer', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:customers,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $ids = $request->input('ids');
            Customer::whereIn('id', $ids)->delete();

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
