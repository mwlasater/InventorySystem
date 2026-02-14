<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('items')->orderBy('name')->paginate(50);
        return view('tags.index', compact('tags'));
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:tags,name,' . $tag->id,
        ]);

        $tag->update(['name' => $request->name, 'slug' => \Illuminate\Support\Str::slug($request->name)]);
        return back()->with('success', "Tag renamed to '{$tag->name}'.");
    }

    public function destroy(Tag $tag)
    {
        $name = $tag->name;
        $tag->items()->detach();
        $tag->delete();
        return back()->with('success', "Tag '{$name}' deleted.");
    }

    public function merge(Request $request)
    {
        $request->validate([
            'source_id' => 'required|exists:tags,id',
            'target_id' => 'required|exists:tags,id|different:source_id',
        ]);

        $source = Tag::findOrFail($request->source_id);
        $target = Tag::findOrFail($request->target_id);

        $source->items()->each(function ($item) use ($target) {
            if (!$item->tags()->where('tags.id', $target->id)->exists()) {
                $item->tags()->attach($target->id);
            }
        });

        $source->items()->detach();
        $sourceName = $source->name;
        $source->delete();

        return back()->with('success', "Tag '{$sourceName}' merged into '{$target->name}'.");
    }
}
