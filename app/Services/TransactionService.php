<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function create(Item $item, array $data): Transaction
    {
        return DB::transaction(function () use ($item, $data) {
            // Calculate net proceeds if applicable
            if (isset($data['sale_price'])) {
                $data['net_proceeds'] = ($data['sale_price'] ?? 0) - ($data['shipping_cost'] ?? 0);
            }

            $data['item_id'] = $item->id;
            $data['created_by'] = auth()->id();

            $transaction = Transaction::create($data);

            // Update item status based on transaction type
            $oldStatus = $item->status;
            $newStatus = $transaction->getResultingStatus();

            $item->update([
                'status' => $newStatus,
                'modified_by' => auth()->id(),
            ]);

            // Audit log
            AuditLog::record(
                'transaction_created',
                $item,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'transaction_type' => $transaction->transaction_type]
            );

            return $transaction;
        });
    }

    public function validateTransactionAllowed(Item $item, string $transactionType): ?string
    {
        // Can always create a status correction
        if ($transactionType === 'status_correction') {
            return null;
        }

        // Returned only if currently loaned out
        if ($transactionType === 'returned' && $item->status !== 'loaned_out') {
            return 'This item is not currently loaned out.';
        }

        // Disposition types require item to be in a non-disposition status (or loaned)
        if (in_array($transactionType, Transaction::DISPOSITION_TYPES)) {
            if ($item->isDispositionStatus() && $item->status !== 'loaned_out') {
                return "This item is already in '{$item->status_label}' status. Create a 'Status Correction' or 'Returned' transaction first to bring it back to collection.";
            }
        }

        return null;
    }
}
