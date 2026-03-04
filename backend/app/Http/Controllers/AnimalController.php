<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Animal::where('farm_id', $request->user()->farm_id)->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'count' => 'required|integer|min:1',
            'breed' => 'required|string',
            'age_group' => 'required|string',
            'status' => 'required|in:healthy,sick,quarantined,deceased',
        ]);

        $validated['farm_id'] = $request->user()->farm_id;
        $animal = Animal::create($validated);

        return response()->json($animal, 201);
    }

    public function show(Animal $animal)
    {
        return response()->json($animal);
    }

    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'count' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:healthy,sick,quarantined,deceased',
            'notes' => 'sometimes|string',
        ]);

        $animal->update($validated);

        return response()->json($animal);
    }

    public function destroy(Animal $animal)
    {
        $animal->delete();

        return response()->json(['message' => 'Animal deleted']);
    }
}
