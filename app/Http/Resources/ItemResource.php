<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Item
 */
class ItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'brand' => $this->brand,
            'model_number' => $this->model_number,
            'condition' => $this->condition_rating,
            'condition_label' => $this->condition_label,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'purchase_price' => $this->purchase_price,
            'purchase_currency' => $this->purchase_currency,
            'estimated_value' => $this->estimated_value,
            'valuation_date' => $this->valuation_date?->toDateString(),
            'is_favorite' => $this->is_favorite,
            'category' => $this->whenLoaded('category', fn () => $this->category?->only(['id', 'name'])),
            'location' => $this->whenLoaded('location', fn () => $this->location?->only(['id', 'name'])),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
