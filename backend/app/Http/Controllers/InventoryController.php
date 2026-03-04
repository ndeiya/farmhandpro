<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(Inventory::where('farm_id', $request->user()->farm_id)->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'reorder_level' => 'nullable|numeric|min:0',
        ]);

        $validated['farm_id'] = $request->user()->farm_id;
        $validated['last_updated'] = now();
        $inventory = Inventory::create($validated);

        return response()->json($inventory, 201);
    }

    public function show(Inventory $inventory)
    {
        return response()->json($inventory);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|numeric|min:0',
            'reorder_level' => 'sometimes|numeric|min:0',
            'notes' => 'sometimes|string',
        ]);

        $validated['last_updated'] = now();
        $inventory->update($validated);

        return response()->json($inventory);
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();

        return response()->json(['message' => 'Inventory deleted']);
    }
}
