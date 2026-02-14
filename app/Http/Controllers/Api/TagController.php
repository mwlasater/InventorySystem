<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $tags = Tag::where('name', 'like', "%{$query}%")
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name']);

        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $tag = Tag::findOrCreateByName($request->name);
        return response()->json($tag);
    }
}
