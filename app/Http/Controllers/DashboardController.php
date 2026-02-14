<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::active()->count();
        $totalValue = Item::active()->sum('estimated_value');
        $totalCostBasis = Item::active()->sum('purchase_price');
        $gainLoss = $totalValue - $totalCostBasis;

        $recentItems = Item::active()
            ->with('category', 'location')
            ->latest()
            ->limit(5)
            ->get();

        $loanedItems = Item::active()
            ->where('status', 'loaned_out')
            ->with('location')
            ->get();

        $overdueLoans = Transaction::where('transaction_type', 'loaned_out')
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', now())
            ->whereHas('item', fn ($q) => $q->where('status', 'loaned_out'))
            ->with('item')
            ->get();

        $itemsByStatus = Item::active()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $itemsByCategory = Category::withCount(['items' => fn ($q) => $q->active()])
            ->having('items_count', '>', 0)
            ->orderByDesc('items_count')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalItems', 'totalValue', 'totalCostBasis', 'gainLoss',
            'recentItems', 'loanedItems', 'overdueLoans',
            'itemsByStatus', 'itemsByCategory'
        ));
    }

    public function chartData()
    {
        $itemsByCategory = Category::withCount(['items' => fn ($q) => $q->active()])
            ->having('items_count', '>', 0)
            ->orderByDesc('items_count')
            ->limit(10)
            ->get()
            ->map(fn ($c) => ['label' => $c->name, 'value' => $c->items_count]);

        $valueByCategory = Category::query()
            ->join('items', 'categories.id', '=', 'items.category_id')
            ->where('items.is_deleted', false)
            ->selectRaw('categories.name, SUM(items.estimated_value) as total_value')
            ->groupBy('categories.name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get()
            ->map(fn ($c) => ['label' => $c->name, 'value' => (float) $c->total_value]);

        return response()->json([
            'itemsByCategory' => $itemsByCategory,
            'valueByCategory' => $valueByCategory,
        ]);
    }
}
