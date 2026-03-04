<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use Illuminate\Http\Request;

class CropController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Crop::where('farm_id', $request->user()->farm_id)->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'variety' => 'required|string',
            'acres' => 'required|numeric|min:0',
            'planted_date' => 'required|date',
            'expected_harvest' => 'nullable|date',
            'status' => 'required|in:planning,planted,growing,ready,harvested',
        ]);

        $validated['farm_id'] = $request->user()->farm_id;
        $crop = Crop::create($validated);

        return response()->json($crop, 201);
    }

    public function show(Crop $crop)
    {
        return response()->json($crop);
    }

    public function update(Request $request, Crop $crop)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'variety' => 'sometimes|string',
            'acres' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:planning,planted,growing,ready,harvested',
            'yield_estimate' => 'sometimes|numeric|min:0',
        ]);

        $crop->update($validated);

        return response()->json($crop);
    }

    public function destroy(Crop $crop)
    {
        $crop->delete();

        return response()->json(['message' => 'Crop deleted']);
    }
}
