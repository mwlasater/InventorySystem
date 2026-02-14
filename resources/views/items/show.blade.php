@extends('layouts.app')
@section('title', $item->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('items.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Back to Inventory</a>
</div>

<div class="flex items-start justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
            {{ $item->name }}
            @if($item->is_favorite)
                <svg class="w-6 h-6 text-yellow-500 ml-2" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            @endif
        </h2>
        <p class="text-gray-500 mt-1">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $item->status === 'in_collection' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $item->status_label }}</span>
            @if($item->category) <span class="ml-2">{{ $item->category->full_name }}</span> @endif
        </p>
    </div>
    <div class="flex space-x-2">
        <form method="POST" action="{{ route('items.favorite', $item) }}">
            @csrf
            <button type="submit" class="p-2 rounded-md hover:bg-gray-100" title="{{ $item->is_favorite ? 'Remove from favorites' : 'Add to favorites' }}">
                <svg class="w-5 h-5 {{ $item->is_favorite ? 'text-yellow-500' : 'text-gray-400' }}" fill="{{ $item->is_favorite ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            </button>
        </form>
        <a href="{{ route('items.edit', $item) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">Edit</a>
        <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Move this item to trash?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md text-sm">Delete</button>
        </form>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: 'overview' }" class="bg-white rounded-lg shadow">
    <div class="border-b border-gray-200">
        <nav class="flex -mb-px">
            <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm">Overview</button>
            <button @click="tab = 'details'" :class="tab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm">Details</button>
            <button @click="tab = 'transactions'" :class="tab === 'transactions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm">Transactions</button>
            <button @click="tab = 'documents'" :class="tab === 'documents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm">Documents</button>
            <button @click="tab = 'history'" :class="tab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm">History</button>
        </nav>
    </div>

    {{-- Overview Tab --}}
    <div x-show="tab === 'overview'" class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Photo Gallery --}}
            <div>
                @if($item->photos->count())
                    <div class="rounded-lg overflow-hidden bg-gray-100 h-64 flex items-center justify-center mb-4">
                        <img src="{{ asset('storage/' . ($item->primary_photo->thumbnail_md ?? $item->primary_photo->file_path)) }}" alt="{{ $item->name }}" class="max-h-full object-contain">
                    </div>
                @else
                    <div class="rounded-lg bg-gray-100 h-64 flex items-center justify-center mb-4">
                        <span class="text-gray-400">No photos</span>
                    </div>
                @endif
            </div>

            {{-- Key Info --}}
            <div class="space-y-4">
                @if($item->description)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Description</h4>
                        <p class="mt-1 text-gray-800">{{ $item->description }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Location</h4>
                        <p class="mt-1 text-gray-800">{{ $item->location?->full_path ?? 'Not set' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Condition</h4>
                        <p class="mt-1 text-gray-800">{{ $item->condition_label ?? 'Not rated' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Quantity</h4>
                        <p class="mt-1 text-gray-800">{{ $item->quantity }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Estimated Value</h4>
                        <p class="mt-1 text-gray-800 font-semibold">{{ $item->estimated_value ? '$'.number_format($item->estimated_value, 2) : 'Not set' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Purchase Price</h4>
                        <p class="mt-1 text-gray-800">{{ $item->purchase_price ? '$'.number_format($item->purchase_price, 2) : 'Not set' }}</p>
                    </div>
                    @if($item->gain_loss !== null)
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Gain/Loss</h4>
                            <p class="mt-1 font-semibold {{ $item->gain_loss >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                {{ $item->gain_loss >= 0 ? '+' : '' }}${{ number_format($item->gain_loss, 2) }}
                            </p>
                        </div>
                    @endif
                </div>

                @if($item->tags->count())
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Tags</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($item->tags as $tag)
                                <span class="inline-flex px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Details Tab --}}
    <div x-show="tab === 'details'" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
            @php
                $details = [
                    'SKU' => $item->sku,
                    'Barcode' => $item->barcode,
                    'Brand' => $item->brand,
                    'Model / Part Number' => $item->model_number,
                    'Year Manufactured' => $item->year_manufactured,
                    'Color' => $item->color,
                    'Dimensions' => $item->dimensions,
                    'Acquisition Date' => $item->acquisition_date?->format('M j, Y'),
                    'Acquisition Source' => $item->acquisition_source,
                    'Acquisition Method' => $item->acquisition_method_label,
                    'Purchase Currency' => $item->purchase_currency,
                    'Valuation Date' => $item->valuation_date?->format('M j, Y'),
                    'Valuation Source' => $item->valuation_source,
                    'Date Added' => $item->created_at?->format('M j, Y g:i A'),
                    'Last Modified' => $item->updated_at?->format('M j, Y g:i A'),
                    'Created By' => $item->createdBy?->display_identifier,
                    'Modified By' => $item->modifiedBy?->display_identifier,
                ];
            @endphp
            @foreach($details as $label => $value)
                @if($value)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ $label }}</h4>
                        <p class="mt-1 text-gray-800">{{ $value }}</p>
                    </div>
                @endif
            @endforeach
        </div>
        @if($item->notes)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Notes</h4>
                <p class="text-gray-800 whitespace-pre-wrap">{{ $item->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Transactions Tab --}}
    <div x-show="tab === 'transactions'" class="p-6">
        <p class="text-gray-500 text-sm">Transaction tracking will be available in a future update.</p>
    </div>

    {{-- Documents Tab --}}
    <div x-show="tab === 'documents'" class="p-6">
        <p class="text-gray-500 text-sm">Document management will be available in a future update.</p>
    </div>

    {{-- History Tab --}}
    <div x-show="tab === 'history'" class="p-6">
        <p class="text-gray-500 text-sm">Audit history will be available in a future update.</p>
    </div>
</div>
@endsection
