<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Equipment::where('farm_id', $request->user()->farm_id)->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|string',
            'serial_number' => 'nullable|string|unique:equipment',
            'purchase_date' => 'required|date',
            'status' => 'required|in:operational,maintenance,repair,retired',
        ]);

        $validated['farm_id'] = $request->user()->farm_id;
        $equipment = Equipment::create($validated);

        return response()->json($equipment, 201);
    }

    public function show(Equipment $equipment)
    {
        return response()->json($equipment);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:operational,maintenance,repair,retired',
            'last_maintenance' => 'sometimes|date',
            'notes' => 'sometimes|string',
        ]);

        $equipment->update($validated);

        return response()->json($equipment);
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return response()->json(['message' => 'Equipment deleted']);
    }
}
