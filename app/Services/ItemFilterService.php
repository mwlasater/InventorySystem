<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ItemFilterService
{
    public function apply(Builder $query, Request $request): Builder
    {
        // Text search (FULLTEXT)
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('MATCH(name, description, notes) AGAINST(? IN BOOLEAN MODE)', [$search . '*'])
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model_number', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Location filter (include descendants)
        if ($request->filled('location_id')) {
            $location = Location::find($request->location_id);
            if ($location) {
                $query->whereIn('location_id', $location->getAllDescendantIds());
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Condition filter
        if ($request->filled('condition_rating')) {
            $query->where('condition_rating', $request->condition_rating);
        }

        // Tags filter
        if ($request->filled('tags')) {
            $tagIds = is_array($request->tags) ? $request->tags : [$request->tags];
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand', 'like', "%{$request->brand}%");
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('acquisition_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('acquisition_date', '<=', $request->date_to);
        }

        // Value range filter
        if ($request->filled('value_min')) {
            $query->where('estimated_value', '>=', $request->value_min);
        }
        if ($request->filled('value_max')) {
            $query->where('estimated_value', '<=', $request->value_max);
        }

        // Has photos filter
        if ($request->filled('has_photos')) {
            if ($request->has_photos === '1') {
                $query->whereHas('photos');
            } else {
                $query->whereDoesntHave('photos');
            }
        }

        // Favorites only
        if ($request->filled('favorites') && $request->favorites === '1') {
            $query->where('is_favorite', true);
        }

        return $query;
    }

    public function applySorting(Builder $query, Request $request): Builder
    {
        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');

        $allowedSorts = ['name', 'created_at', 'updated_at', 'estimated_value', 'purchase_price', 'acquisition_date', 'status', 'quantity'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        return $query->orderBy($sortField, $sortDir);
    }
}
