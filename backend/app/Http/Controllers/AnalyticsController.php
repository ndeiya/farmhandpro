<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Report;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'total_workers' => 0,
            'total_hours_this_month' => 0,
            'attendance_rate' => 0,
            'pending_reports' => Report::where('status', 'draft')->count(),
        ]);
    }

    public function attendanceSummary(Request $request)
    {
        $days = $request->query('days', 30);

        $summary = Attendance::whereBetween('date', [now()->subDays($days), now()])
            ->selectRaw('DATE(date) as date, COUNT(*) as total, SUM(HOUR(TIMEDIFF(clock_out_time, clock_in_time))) as hours')
            ->groupBy('date')
            ->get();

        return response()->json($summary);
    }

    public function productivity(Request $request)
    {
        $period = $request->query('period', 'monthly');

        return response()->json([
            'message' => 'Productivity data',
            'data' => [],
        ]);
    }
}
