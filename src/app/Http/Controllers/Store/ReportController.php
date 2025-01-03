<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order\Order;
use App\Models\Store\DutyShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class ReportController extends Controller
{
    public function handoverReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'shift_id' => 'required|integer|exists:duty_shifts,id'
            ]);

            // Fetch the shift details
            $shift = DutyShift::findOrFail($validated['shift_id']);

            // Use the shift's time range to filter today's orders
            $orders = Order::whereTime('created_at', '>=', $shift->start_time)
                ->whereTime('created_at', '<=', $shift->end_time)
                ->whereDate('created_at', now()->toDateString())
                ->selectRaw('HOUR(created_at) as hour, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('hour')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function hourlyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_time' => 'required|date_format:Y-m-d H:i:s',
                'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            ]);

            $orders = Order::whereBetween('created_at', [$validated['start_time'], $validated['end_time']])
                ->selectRaw('HOUR(created_at) as hour, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('hour')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function dailyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'date' => 'required|date',
            ]);

            $orders = Order::whereDate('created_at', $validated['date'])
                ->selectRaw('HOUR(created_at) as hour, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('hour')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function weeklyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_date' => 'required|date',
            ]);

            $startDate = now()->parse($validated['start_date'])->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();

            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('date')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function monthlyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'month' => 'required|date_format:Y-m',
            ]);

            $orders = Order::whereYear('created_at', now()->parse($validated['month'])->year)
                ->whereMonth('created_at', now()->parse($validated['month'])->month)
                ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('date')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function quarterlyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:1900|max:'.now()->year,
                'quarter' => 'required|integer|min:1|max:4',
            ]);

            $startMonth = ($validated['quarter'] - 1) * 3 + 1;
            $startDate = now()->create($validated['year'], $startMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->addMonths(2)->endOfMonth();

            $orders = Order::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('date')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    public function yearlyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:1900|max:'.now()->year,
            ]);

            $orders = Order::whereYear('created_at', $validated['year'])
                ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_sales, COUNT(id) as total_orders')
                ->groupBy('date')
                ->get();

            return response()->json(['code' => http_response_code(), 'data' => ['report' => $orders]]);
        } catch (Exception $e) {
            return response()->json(['code' => http_response_code(), 'data' => $e->getMessage()], 500);
        }
    }

    private function generateSummary($orders)
    {
        return [
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total_price'),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
        ];
    }
}
