<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Item;
use Illuminate\Http\Request;

class BulkItemController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'exists:items,id',
            'action' => 'required|in:change_status,change_category,change_location,delete',
        ]);

        $items = Item::active()->whereIn('id', $request->item_ids)->get();

        switch ($request->action) {
            case 'change_status':
                $request->validate(['status' => 'required|in:in_collection,damaged']);
                foreach ($items as $item) {
                    $old = $item->toArray();
                    $item->update(['status' => $request->status, 'modified_by' => auth()->id()]);
                    AuditLog::record('update', 'items', $item->id, $old, $item->fresh()->toArray());
                }
                return back()->with('success', count($items) . ' items updated.');

            case 'change_category':
                $request->validate(['category_id' => 'required|exists:categories,id']);
                foreach ($items as $item) {
                    $old = $item->toArray();
                    $item->update(['category_id' => $request->category_id, 'modified_by' => auth()->id()]);
                    AuditLog::record('update', 'items', $item->id, $old, $item->fresh()->toArray());
                }
                return back()->with('success', count($items) . ' items updated.');

            case 'change_location':
                $request->validate(['location_id' => 'required|exists:locations,id']);
                foreach ($items as $item) {
                    $old = $item->toArray();
                    $item->update(['location_id' => $request->location_id, 'modified_by' => auth()->id()]);
                    AuditLog::record('update', 'items', $item->id, $old, $item->fresh()->toArray());
                }
                return back()->with('success', count($items) . ' items updated.');

            case 'delete':
                foreach ($items as $item) {
                    $old = $item->toArray();
                    $item->softDelete();
                    AuditLog::record('delete', 'items', $item->id, $old, null);
                }
                return back()->with('success', count($items) . ' items moved to trash.');
        }
    }
}
