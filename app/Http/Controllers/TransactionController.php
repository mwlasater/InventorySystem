<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(protected TransactionService $transactionService)
    {
    }

    public function create(Item $item, Request $request)
    {
        $preselectedType = $request->query('type');

        return view('transactions.create', [
            'item' => $item->load('category', 'location'),
            'transactionTypes' => Transaction::TYPE_LABELS,
            'preselectedType' => $preselectedType,
        ]);
    }

    public function store(Request $request, Item $item)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:' . implode(',', array_keys(Transaction::TYPE_LABELS)),
            'transaction_date' => 'required|date|before_or_equal:today',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_contact' => 'nullable|string|max:255',
            'sale_price' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'platform' => 'nullable|string|max:100',
            'expected_return_date' => 'nullable|date|after:transaction_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Validate this transaction is allowed for the item's current state
        $error = $this->transactionService->validateTransactionAllowed($item, $validated['transaction_type']);
        if ($error) {
            return back()->withErrors(['transaction_type' => $error])->withInput();
        }

        $this->transactionService->create($item, $validated);

        return redirect()->route('items.show', $item)
            ->with('success', 'Transaction recorded. Item status updated to ' . $item->fresh()->status_label . '.');
    }
}
