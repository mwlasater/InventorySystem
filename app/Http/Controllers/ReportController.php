<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\Transaction;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ExportService $exportService)
    {
    }

    public function index()
    {
        return view('reports.index');
    }

    public function collectionSummary(Request $request)
    {
        $categories = Category::withCount(['items' => fn ($q) => $q->active()])
            ->with(['items' => fn ($q) => $q->active()])
            ->get()
            ->map(fn ($c) => [
                'name' => $c->full_name,
                'count' => $c->items_count,
                'total_value' => $c->items->sum('estimated_value'),
                'total_cost' => $c->items->sum('purchase_price'),
            ])
            ->filter(fn ($c) => $c['count'] > 0)
            ->sortByDesc('count');

        if ($request->query('format') === 'csv') {
            return $this->exportService->toCsv($categories, 'collection-summary.csv', ['Category', 'Count', 'Total Value', 'Total Cost']);
        }
        if ($request->query('format') === 'pdf') {
            return $this->exportService->toPdf('reports.pdf.collection-summary', ['categories' => $categories], 'collection-summary.pdf');
        }

        return view('reports.collection-summary', compact('categories'));
    }

    public function valuationReport(Request $request)
    {
        $items = Item::active()
            ->whereNotNull('estimated_value')
            ->with('category')
            ->orderByDesc('estimated_value')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'category' => $item->category?->name,
                'purchase_price' => $item->purchase_price,
                'estimated_value' => $item->estimated_value,
                'gain_loss' => $item->gain_loss,
            ]);

        if ($request->query('format') === 'csv') {
            return $this->exportService->toCsv($items, 'valuation-report.csv', ['Item', 'Category', 'Purchase Price', 'Estimated Value', 'Gain/Loss']);
        }
        if ($request->query('format') === 'pdf') {
            return $this->exportService->toPdf('reports.pdf.valuation', ['items' => $items], 'valuation-report.pdf');
        }

        return view('reports.valuation', compact('items'));
    }

    public function transactionReport(Request $request)
    {
        $query = Transaction::with('item', 'creator');

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderByDesc('transaction_date')->get();
        $totalProceeds = $transactions->sum('net_proceeds');

        if ($request->query('format') === 'csv') {
            $data = $transactions->map(fn ($t) => [
                'date' => $t->transaction_date->format('Y-m-d'),
                'item' => $t->item?->name,
                'type' => $t->type_label,
                'sale_price' => $t->sale_price,
                'shipping_cost' => $t->shipping_cost,
                'net_proceeds' => $t->net_proceeds,
                'recipient' => $t->recipient_name,
                'platform' => $t->platform,
            ]);
            return $this->exportService->toCsv($data, 'transaction-report.csv', ['Date', 'Item', 'Type', 'Sale Price', 'Shipping', 'Net Proceeds', 'Recipient', 'Platform']);
        }

        return view('reports.transactions', compact('transactions', 'totalProceeds'));
    }

    public function locationInventory(Request $request)
    {
        $locations = Location::whereNull('parent_id')
            ->with(['children.children.children', 'items' => fn ($q) => $q->active()])
            ->withCount(['items' => fn ($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        return view('reports.location-inventory', compact('locations'));
    }

    public function statusBreakdown(Request $request)
    {
        $statuses = Item::active()
            ->selectRaw('status, COUNT(*) as count, SUM(estimated_value) as total_value')
            ->groupBy('status')
            ->get()
            ->map(fn ($s) => [
                'status' => Item::STATUS_LABELS[$s->status] ?? $s->status,
                'count' => $s->count,
                'total_value' => $s->total_value,
            ]);

        return view('reports.status-breakdown', compact('statuses'));
    }

    public function acquisitionHistory(Request $request)
    {
        $query = Item::active()->with('category');

        if ($request->filled('date_from')) {
            $query->where('acquisition_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('acquisition_date', '<=', $request->date_to);
        }

        $items = $query->orderByDesc('acquisition_date')->get();

        return view('reports.acquisition-history', compact('items'));
    }
}
