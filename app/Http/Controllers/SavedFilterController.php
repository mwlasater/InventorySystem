<?php

namespace App\Http\Controllers;

use App\Models\SavedFilter;
use Illuminate\Http\Request;

class SavedFilterController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'criteria' => 'required|array',
        ]);

        $filter = SavedFilter::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'criteria' => $validated['criteria'],
        ]);

        return response()->json(['id' => $filter->id, 'name' => $filter->name]);
    }

    public function index()
    {
        $filters = SavedFilter::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();

        return response()->json($filters);
    }

    public function destroy(SavedFilter $filter)
    {
        if ($filter->user_id !== auth()->id()) {
            abort(403);
        }

        $filter->delete();
        return response()->json(['success' => true]);
    }
}
