<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Http\Resources\TransactionResource;
use App\Models\Item;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    public function __construct(private TransactionService $transactions) {}

    /**
     * Record a transaction against an item (which may update its status).
     */
    public function store(Request $request, Item $item)
    {
        abort_if($item->is_deleted, 404);

        $validated = $request->validate([
            'transaction_type' => 'required|in:'.implode(',', array_keys(Transaction::TYPE_LABELS)),
            'transaction_date' => 'required|date|before_or_equal:today',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_contact' => 'nullable|string|max:255',
            'sale_price' => 'nullable|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'platform' => 'nullable|string|max:100',
            'expected_return_date' => 'nullable|date|after:transaction_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        // Reject transactions that aren't valid for the item's current state
        // (e.g. selling an already-sold item) as a 422.
        if ($error = $this->transactions->validateTransactionAllowed($item, $validated['transaction_type'])) {
            throw ValidationException::withMessages(['transaction_type' => $error]);
        }

        $transaction = $this->transactions->create($item, $validated);

        return (new TransactionResource($transaction))
            ->additional(['item' => new ItemResource($item->fresh()->load(['category', 'location', 'tags']))])
            ->response()
            ->setStatusCode(201);
    }
}
