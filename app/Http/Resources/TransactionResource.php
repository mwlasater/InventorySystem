<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Transaction
 */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'transaction_type' => $this->transaction_type,
            'type_label' => $this->type_label,
            'transaction_date' => $this->transaction_date?->toDateString(),
            'recipient_name' => $this->recipient_name,
            'recipient_contact' => $this->recipient_contact,
            'sale_price' => $this->sale_price,
            'shipping_cost' => $this->shipping_cost,
            'net_proceeds' => $this->net_proceeds,
            'platform' => $this->platform,
            'expected_return_date' => $this->expected_return_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
