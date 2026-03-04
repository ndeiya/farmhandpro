<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::query();

        if ($request->user()->role !== 'owner' && $request->user()->role !== 'supervisor') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->paginate(15));
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $today = now()->toDateString();
        $existingAttendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance && !$existingAttendance->clock_out_time) {
            return response()->json(['error' => 'Already clocked in'], 400);
        }

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'clock_in_time' => now(),
            'date' => $today,
            'notes' => $request->notes,
            'status' => 'active',
        ]);

        return response()->json($attendance, 201);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        if ($attendance->user_id !== Auth::id() && Auth::user()->role === 'worker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($attendance->clock_out_time) {
            return response()->json(['error' => 'Already clocked out'], 400);
        }

        $attendance->update([
            'clock_out_time' => now(),
            'status' => 'completed',
        ]);

        return response()->json($attendance);
    }

    public function current(Request $request)
    {
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('date', $today)
            ->where('clock_out_time', null)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Not clocked in'], 404);
        }

        return response()->json($attendance);
    }

    public function history(Request $request)
    {
        $days = $request->query('days', 30);

        $history = Attendance::where('user_id', Auth::id())
            ->whereBetween('date', [now()->subDays($days), now()])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($history);
    }

    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($attendance->user_id !== Auth::id() && Auth::user()->role === 'worker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($attendance);
    }
}
