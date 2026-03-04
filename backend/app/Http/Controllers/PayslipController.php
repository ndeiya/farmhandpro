<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $query = Payslip::query();

        if ($request->user()->role === 'worker') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->orderBy('period_end', 'desc')->paginate(12));
    }

    public function show($id)
    {
        $payslip = Payslip::findOrFail($id);

        if ($payslip->user_id !== Auth::id() && Auth::user()->role === 'worker') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($payslip);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Payslip generation logic
        return response()->json(['message' => 'Payslips generated successfully']);
    }
}
