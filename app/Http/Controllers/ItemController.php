<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Tag;
use App\Services\DuplicateDetectionService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::active()->with(['category', 'location', 'photos']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('location_id')) {
            $location = Location::find($request->location_id);
            if ($location) {
                $query->whereIn('location_id', $location->getAllDescendantIds());
            }
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('favorites')) {
            $query->where('is_favorite', true);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['name', 'created_at', 'estimated_value', 'purchase_price', 'quantity'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = in_array($request->get('per_page'), [25, 50, 100]) ? $request->get('per_page') : 25;
        $items = $query->paginate($perPage)->withQueryString();

        $viewMode = $request->get('view', session('item_view_mode', 'grid'));
        session(['item_view_mode' => $viewMode]);

        $categories = Category::topLevel()->with('children')->get();
        $locations = Location::topLevel()->get();

        return view('items.index', compact('items', 'viewMode', 'categories', 'locations'));
    }

    public function create()
    {
        $categories = Category::topLevel()->with('children')->get();
        $locations = Location::topLevel()->with(['children.children.children'])->get();
        return view('items.create', compact('categories', 'locations'));
    }

    public function store(StoreItemRequest $request, DuplicateDetectionService $duplicateService)
    {
        // Check for duplicates unless overridden
        if (!$request->boolean('skip_duplicate_check')) {
            $duplicates = $duplicateService->findPotentialDuplicates($request->validated());
            if ($duplicates->isNotEmpty()) {
                return back()->withInput()->with('duplicates', $duplicates)->with('warning', 'Potential duplicates found. Review and save again to override.');
            }
        }

        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['modified_by'] = auth()->id();
        unset($data['tags']);

        $item = Item::create($data);

        // Sync tags
        if ($request->has('tags')) {
            $tagIds = collect($request->tags)->map(fn($name) => Tag::findOrCreateByName($name)->id);
            $item->tags()->sync($tagIds);
        }

        AuditLog::record('create', 'items', $item->id, null, $item->toArray());

        return redirect()->route('items.show', $item)->with('success', "Item '{$item->name}' created.");
    }

    public function show(Item $item)
    {
        $item->load(['category', 'location', 'tags', 'photos', 'documents', 'transactions', 'createdBy', 'modifiedBy']);
        $auditLogs = $item->auditLog()->with('user')->limit(50)->get();
        return view('items.show', compact('item', 'auditLogs'));
    }

    public function edit(Item $item)
    {
        $item->load('tags');
        $categories = Category::topLevel()->with('children')->get();
        $locations = Location::topLevel()->with(['children.children.children'])->get();
        return view('items.edit', compact('item', 'categories', 'locations'));
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $old = $item->toArray();
        $data = $request->validated();
        $data['modified_by'] = auth()->id();

        // Track location change
        $locationChanged = isset($data['location_id']) && $data['location_id'] != $item->location_id;

        unset($data['tags']);
        $item->update($data);

        // Sync tags
        if ($request->has('tags')) {
            $tagIds = collect($request->tags)->map(fn($name) => Tag::findOrCreateByName($name)->id);
            $item->tags()->sync($tagIds);
        } else {
            $item->tags()->detach();
        }

        AuditLog::record('update', 'items', $item->id, $old, $item->fresh()->toArray());

        return redirect()->route('items.show', $item)->with('success', "Item '{$item->name}' updated.");
    }

    public function destroy(Item $item)
    {
        $old = $item->toArray();
        $item->softDelete();
        AuditLog::record('delete', 'items', $item->id, $old, null);

        return redirect()->route('items.index')->with('success', "Item '{$item->name}' moved to trash.");
    }

    public function toggleFavorite(Item $item)
    {
        $item->update(['is_favorite' => !$item->is_favorite]);
        return back();
    }
}
