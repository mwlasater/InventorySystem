<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\AuditLog;
use App\Models\Item;
use App\Models\Tag;
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

    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['modified_by'] = $request->user()->id;
        unset($data['tags']);

        $item = Item::create($data);
        $this->syncTags($request, $item);

        AuditLog::record('create', 'items', $item->id, null, $item->toArray());

        return (new ItemResource($item->load(['category', 'location', 'tags'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        abort_if($item->is_deleted, 404);

        $old = $item->toArray();
        $data = $request->validated();
        $data['modified_by'] = $request->user()->id;
        unset($data['tags']);

        $item->update($data);
        // Only touch tags when the client sends them, so omitting the key in a
        // partial update doesn't silently wipe an item's tags.
        if ($request->has('tags')) {
            $this->syncTags($request, $item);
        }

        AuditLog::record('update', 'items', $item->id, $old, $item->fresh()->toArray());

        return new ItemResource($item->fresh()->load(['category', 'location', 'tags']));
    }

    public function destroy(Item $item)
    {
        abort_if($item->is_deleted, 404);

        $old = $item->toArray();
        $item->softDelete();
        AuditLog::record('delete', 'items', $item->id, $old, null);

        return response()->noContent();
    }

    private function syncTags(Request $request, Item $item): void
    {
        $tagIds = collect($request->input('tags', []))
            ->map(fn (string $name) => Tag::findOrCreateByName($name)->id);
        $item->tags()->sync($tagIds);
    }
}
