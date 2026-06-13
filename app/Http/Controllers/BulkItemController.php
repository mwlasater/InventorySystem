<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Item;
use App\Models\Tag;
use App\Services\ExportService;
use Illuminate\Http\Request;

class BulkItemController extends Controller
{
    public function __construct(private ExportService $exportService) {}

    public function update(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:items,id',
            'action' => 'required|in:change_status,change_category,change_location,add_tags,remove_tags,delete',
        ]);

        $items = Item::active()->whereIn('id', $request->item_ids)->get();

        switch ($request->action) {
            case 'change_status':
                // Limited to non-disposition statuses: sold/loaned_out/etc. must go
                // through the transaction flow so a transaction record is created.
                $request->validate(['status' => 'required|in:in_collection,damaged']);

                return $this->applyUpdate($items, ['status' => $request->status]);

            case 'change_category':
                $request->validate(['category_id' => 'required|exists:categories,id']);

                return $this->applyUpdate($items, ['category_id' => $request->category_id]);

            case 'change_location':
                $request->validate(['location_id' => 'required|exists:locations,id']);

                return $this->applyUpdate($items, ['location_id' => $request->location_id]);

            case 'add_tags':
                return $this->changeTags($request, $items, attach: true);

            case 'remove_tags':
                return $this->changeTags($request, $items, attach: false);

            case 'delete':
                foreach ($items as $item) {
                    $old = $item->toArray();
                    $item->softDelete();
                    AuditLog::record('delete', 'items', $item->id, $old, null);
                }

                return back()->with('success', count($items).' items moved to trash.');
        }
    }

    /**
     * Restore selected items from the trash.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:items,id',
        ]);

        $items = Item::trashed()->whereIn('id', $request->item_ids)->get();

        foreach ($items as $item) {
            $item->restore();
            AuditLog::record('restore', 'items', $item->id, null, $item->toArray());
        }

        return back()->with('success', count($items).' items restored.');
    }

    /**
     * Export the selected items to CSV (same columns as the full export).
     */
    public function export(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:items,id',
        ]);

        $items = Item::active()
            ->whereIn('id', $request->item_ids)
            ->with(['category', 'location', 'tags'])
            ->get();

        return $this->exportService->itemsToCsv($items, 'inventory-selection-'.now()->format('Y-m-d').'.csv');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Item>  $items
     */
    private function applyUpdate($items, array $attributes)
    {
        foreach ($items as $item) {
            $old = $item->toArray();
            $item->update($attributes + ['modified_by' => auth()->id()]);
            AuditLog::record('update', 'items', $item->id, $old, $item->fresh()->toArray());
        }

        return back()->with('success', count($items).' items updated.');
    }

    /**
     * Attach or detach a set of tags (given by name) across the selected items.
     *
     * @param  \Illuminate\Support\Collection<int, Item>  $items
     */
    private function changeTags(Request $request, $items, bool $attach)
    {
        $request->validate([
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:50',
        ]);

        $tagIds = collect($request->tags)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->map(fn (string $name) => Tag::findOrCreateByName($name)->id)
            ->all();

        foreach ($items as $item) {
            if ($attach) {
                $item->tags()->syncWithoutDetaching($tagIds);
            } else {
                $item->tags()->detach($tagIds);
            }
        }

        $verb = $attach ? 'tagged' : 'untagged';

        return back()->with('success', count($items)." items {$verb}.");
    }
}
