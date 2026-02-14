<?php

namespace App\Http\Requests;

use App\Models\Item;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $itemId = $this->route('item')->id;
        $validStatuses = array_diff(
            array_keys(Item::STATUS_LABELS),
            Item::DISPOSITION_STATUSES
        );

        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'sku' => 'nullable|string|max:100|unique:items,sku,' . $itemId,
            'barcode' => 'nullable|string|max:255',
            'condition_rating' => 'nullable|in:' . implode(',', array_keys(Item::CONDITION_LABELS)),
            'brand' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'year_manufactured' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:100',
            'dimensions' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'acquisition_date' => 'nullable|date',
            'acquisition_source' => 'nullable|string|max:255',
            'acquisition_method' => 'nullable|in:' . implode(',', array_keys(Item::ACQUISITION_METHODS)),
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_currency' => 'nullable|string|size:3',
            'estimated_value' => 'nullable|numeric|min:0',
            'valuation_date' => 'nullable|date',
            'valuation_source' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', $validStatuses),
            'location_id' => 'required|exists:locations,id',
            'notes' => 'nullable|string',
            'is_favorite' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'To change status to a disposition type (Sold, Given Away, etc.), use the Record Transaction feature.',
        ];
    }
}
