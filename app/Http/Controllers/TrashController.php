<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Item;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function index()
    {
        $items = Item::trashed()->with(['category', 'location'])->orderBy('deleted_at', 'desc')->paginate(25);
        return view('items.trash', compact('items'));
    }

    public function restore(Item $item)
    {
        $item->restore();
        AuditLog::record('restore', 'items', $item->id, null, $item->toArray());
        return back()->with('success', "Item '{$item->name}' restored.");
    }

    public function forceDelete(Item $item)
    {
        $name = $item->name;
        $item->tags()->detach();
        $item->photos()->delete();
        $item->documents()->delete();
        $item->delete();
        return back()->with('success', "Item '{$name}' permanently deleted.");
    }
}
