<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::query();

        if ($request->user()->role === 'worker') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:crop,animal,equipment,maintenance,other',
            'content' => 'required|string|max:2000',
            'farm_id' => 'nullable|exists:farms,id',
        ]);

        $report = Report::create([
            'user_id' => Auth::id(),
            'farm_id' => $request->farm_id,
            'type' => $request->type,
            'content' => $request->content,
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        return response()->json($report, 201);
    }

    public function show($id)
    {
        $report = Report::findOrFail($id);

        if ($report->user_id !== Auth::id() && Auth::user()->role === 'worker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($report);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        if ($report->user_id !== Auth::id() && Auth::user()->role !== 'owner' && Auth::user()->role !== 'supervisor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,submitted,approved,rejected',
        ]);

        $report->update($validated);

        return response()->json($report);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        if ($report->user_id !== Auth::id() && Auth::user()->role !== 'owner') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $report->delete();

        return response()->json(['message' => 'Report deleted']);
    }
}
