<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::topLevel()->with(['children.children.children'])->get();
        return view('locations.index', compact('locations'));
    }

    public function create(Request $request)
    {
        $parentId = $request->get('parent_id');
        $parent = $parentId ? Location::find($parentId) : null;
        $buildings = Location::buildings()->get();

        $nextLevel = 'building';
        if ($parent) {
            $levelMap = ['building' => 'room', 'room' => 'unit', 'unit' => 'shelf'];
            $nextLevel = $levelMap[$parent->level] ?? 'shelf';
        }

        return view('locations.create', compact('parent', 'buildings', 'nextLevel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:locations,id',
            'level' => 'required|in:building,room,unit,shelf',
            'description' => 'nullable|string',
        ]);

        $location = Location::create($request->only('name', 'parent_id', 'level', 'description'));
        AuditLog::record('create', 'locations', $location->id, null, $location->toArray());

        return redirect()->route('locations.index')->with('success', "Location '{$location->name}' created.");
    }

    public function edit(Location $location)
    {
        $buildings = Location::buildings()->get();
        return view('locations.edit', compact('location', 'buildings'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $old = $location->toArray();
        $location->update($request->only('name', 'description'));
        AuditLog::record('update', 'locations', $location->id, $old, $location->toArray());

        return redirect()->route('locations.index')->with('success', "Location '{$location->name}' updated.");
    }

    public function destroy(Location $location)
    {
        if ($location->items()->exists()) {
            return back()->with('error', 'Cannot delete location with assigned items.');
        }

        $old = $location->toArray();
        $name = $location->name;

        $location->children()->each(function ($child) {
            $child->children()->each(function ($grandchild) {
                $grandchild->children()->delete();
                $grandchild->delete();
            });
            $child->delete();
        });
        $location->delete();

        AuditLog::record('delete', 'locations', $old['id'], $old, null);
        return redirect()->route('locations.index')->with('success', "Location '{$name}' and its children deleted.");
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:locations,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:locations,id',
        ]);

        foreach ($request->items as $item) {
            Location::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Order updated']);
    }
}
