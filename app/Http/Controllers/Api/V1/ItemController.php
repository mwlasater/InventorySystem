<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'category_id' => 'sometimes|integer|exists:categories,id',
            'status' => 'sometimes|string',
            'favorites' => 'sometimes|boolean',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Item::active()->with(['category', 'location', 'tags']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->boolean('favorites')) {
            $query->where('is_favorite', true);
        }

        $sort = in_array($request->get('sort'), ['name', 'created_at', 'estimated_value'])
            ? $request->get('sort')
            : 'created_at';
        $query->orderBy($sort, $request->get('dir') === 'asc' ? 'asc' : 'desc');

        return ItemResource::collection($query->paginate($request->integer('per_page', 25)));
    }

    public function show(Item $item)
    {
        abort_if($item->is_deleted, 404);

        return new ItemResource($item->load(['category', 'location', 'tags']));
    }
}
