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
        <a href="{{ route('items.transactions.create', $item) }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm">Record Transaction</a>
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
        <nav class="flex -mb-px overflow-x-auto">
            <button @click="tab = 'overview'" :class="tab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Overview</button>
            <button @click="tab = 'photos'" :class="tab === 'photos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Photos ({{ $item->photos->count() }})</button>
            <button @click="tab = 'details'" :class="tab === 'details' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Details</button>
            <button @click="tab = 'transactions'" :class="tab === 'transactions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Transactions</button>
            <button @click="tab = 'documents'" :class="tab === 'documents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap">Documents ({{ $item->documents->count() }})</button>
            <button @click="tab = 'history'" :class="tab === 'history' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-3 border-b-2 font-medium text-sm whitespace-nowrap">History</button>
        </nav>
    </div>

    {{-- Overview Tab --}}
    <div x-show="tab === 'overview'" class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Photo Gallery --}}
            <div x-data="{ lightbox: false, currentPhoto: null }" class="relative">
                @if($item->photos->count())
                    <div class="rounded-lg overflow-hidden bg-gray-100 h-72 flex items-center justify-center mb-4 cursor-pointer" @click="currentPhoto = '{{ asset('storage/' . ($item->primary_photo->file_path)) }}'; lightbox = true">
                        <img src="{{ asset('storage/' . ($item->primary_photo->thumbnail_md ?? $item->primary_photo->file_path)) }}" alt="{{ $item->name }}" class="max-h-full max-w-full object-contain">
                    </div>
                    @if($item->photos->count() > 1)
                        <div class="grid grid-cols-5 gap-2">
                            @foreach($item->photos->take(5) as $photo)
                                <div class="aspect-square rounded overflow-hidden bg-gray-100 cursor-pointer" @click="currentPhoto = '{{ asset('storage/' . $photo->file_path) }}'; lightbox = true">
                                    <img src="{{ asset('storage/' . ($photo->thumbnail_sm ?? $photo->file_path)) }}" alt="{{ $photo->caption ?? $item->name }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                        @if($item->photos->count() > 5)
                            <button @click="tab = 'photos'" class="text-blue-600 hover:text-blue-800 text-sm mt-2">View all {{ $item->photos->count() }} photos</button>
                        @endif
                    @endif
                @else
                    <div class="rounded-lg bg-gray-100 h-72 flex flex-col items-center justify-center mb-4">
                        <svg class="w-16 h-16 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-gray-400 text-sm">No photos yet</span>
                        <button @click="tab = 'photos'" class="text-blue-600 hover:text-blue-800 text-sm mt-1">Upload photos</button>
                    </div>
                @endif

                {{-- Lightbox --}}
                <div x-show="lightbox" x-cloak @click.self="lightbox = false" @keydown.escape.window="lightbox = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
                    <button @click="lightbox = false" class="absolute top-4 right-4 text-white hover:text-gray-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <img :src="currentPhoto" class="max-w-full max-h-full object-contain rounded-lg" alt="{{ $item->name }}">
                </div>
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

    {{-- Photos Tab --}}
    <div x-show="tab === 'photos'" class="p-6">
        {{-- Upload Form --}}
        <form method="POST" action="{{ route('items.photos.store', $item) }}" enctype="multipart/form-data" class="mb-6">
            @csrf
            <div x-data="{ dragging: false }" class="border-2 border-dashed rounded-lg p-6 text-center transition-colors" :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.uploadForm.submit()">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="mt-2 text-sm text-gray-600">Drag & drop photos here, or</p>
                <label class="mt-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 cursor-pointer">
                    <span>Choose Files</span>
                    <input type="file" name="photos[]" multiple accept="image/*" capture="environment" class="hidden" x-ref="fileInput" onchange="this.form.submit()">
                </label>
                <p class="mt-1 text-xs text-gray-500">Max {{ \App\Services\PhotoService::MAX_PHOTOS_PER_ITEM }} photos, 10MB each. {{ \App\Services\PhotoService::MAX_PHOTOS_PER_ITEM - $item->photos->count() }} remaining.</p>
            </div>
            @error('photos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('photos.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>

        {{-- Photo Grid --}}
        @if($item->photos->count())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($item->photos as $photo)
                    <div x-data="{ lightbox: false }" class="group relative rounded-lg overflow-hidden bg-gray-100">
                        <div class="aspect-square cursor-pointer" @click="lightbox = true">
                            <img src="{{ asset('storage/' . ($photo->thumbnail_md ?? $photo->file_path)) }}" alt="{{ $photo->caption ?? $item->name }}" class="w-full h-full object-cover">
                        </div>

                        {{-- Primary badge --}}
                        @if($photo->is_primary)
                            <span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-medium px-2 py-0.5 rounded">Primary</span>
                        @endif

                        {{-- Action buttons --}}
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex space-x-1">
                            @if(!$photo->is_primary)
                                <form method="POST" action="{{ route('items.photos.primary', [$item, $photo]) }}">
                                    @csrf
                                    <button type="submit" class="bg-white/90 hover:bg-white rounded p-1 shadow text-xs" title="Set as primary">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('items.photos.destroy', [$item, $photo]) }}" onsubmit="return confirm('Delete this photo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-white/90 hover:bg-white rounded p-1 shadow text-xs" title="Delete photo">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>

                        {{-- Caption --}}
                        @if($photo->caption)
                            <div class="absolute bottom-0 inset-x-0 bg-black/60 px-2 py-1">
                                <p class="text-white text-xs truncate">{{ $photo->caption }}</p>
                            </div>
                        @endif

                        {{-- Lightbox --}}
                        <div x-show="lightbox" x-cloak @click.self="lightbox = false" @keydown.escape.window="lightbox = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4">
                            <button @click="lightbox = false" class="absolute top-4 right-4 text-white hover:text-gray-300">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <img src="{{ asset('storage/' . $photo->file_path) }}" class="max-w-full max-h-full object-contain rounded-lg" alt="{{ $photo->caption ?? $item->name }}">
                            @if($photo->caption)
                                <p class="absolute bottom-8 text-white text-sm bg-black/50 px-4 py-2 rounded">{{ $photo->caption }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No photos uploaded yet. Use the area above to add photos.</p>
        @endif
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
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-800">Transaction History</h3>
            <a href="{{ route('items.transactions.create', $item) }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm">Record Transaction</a>
        </div>

        @if($item->transactions->count())
            <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                @foreach($item->transactions as $transaction)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                    @if($transaction->isDisposition()) bg-red-100 text-red-800
                                    @elseif($transaction->isRestoration()) bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif
                                ">{{ $transaction->type_label }}</span>
                                <span class="text-sm text-gray-500 ml-2">{{ $transaction->transaction_date->format('M j, Y') }}</span>
                                @if($transaction->transaction_type === 'loaned_out' && $transaction->isLoanOverdue())
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 ml-1">OVERDUE</span>
                                @endif
                            </div>
                            @if($transaction->net_proceeds)
                                <span class="text-sm font-semibold text-gray-800">${{ number_format($transaction->net_proceeds, 2) }}</span>
                            @endif
                        </div>
                        @if($transaction->recipient_name)
                            <p class="text-sm text-gray-600 mt-1">Recipient: {{ $transaction->recipient_name }}
                                @if($transaction->recipient_contact) ({{ $transaction->recipient_contact }}) @endif
                            </p>
                        @endif
                        @if($transaction->platform)
                            <p class="text-sm text-gray-600">Platform: {{ $transaction->platform }}</p>
                        @endif
                        @if($transaction->expected_return_date)
                            <p class="text-sm text-gray-600">Expected return: {{ $transaction->expected_return_date->format('M j, Y') }}</p>
                        @endif
                        @if($transaction->notes)
                            <p class="text-sm text-gray-500 mt-1">{{ $transaction->notes }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Recorded by {{ $transaction->creator?->display_identifier ?? 'Unknown' }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No transactions recorded yet.</p>
        @endif

        @if($item->status === 'loaned_out')
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <p class="text-sm text-yellow-800">This item is currently loaned out.
                    <a href="{{ route('items.transactions.create', [$item, 'type' => 'returned']) }}" class="font-medium underline">Record return</a>
                </p>
            </div>
        @endif
    </div>

    {{-- Documents Tab --}}
    <div x-show="tab === 'documents'" class="p-6">
        {{-- Upload Form --}}
        <form method="POST" action="{{ route('items.documents.store', $item) }}" enctype="multipart/form-data" class="mb-6">
            @csrf
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="file" name="document" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="flex-1">
                    <input type="text" name="label" placeholder="Label (optional)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm whitespace-nowrap">Upload</button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Accepted: PDF, Word, Excel, text, CSV, images. Max 20MB.</p>
            @error('document')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </form>

        {{-- Documents List --}}
        @if($item->documents->count())
            <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                @foreach($item->documents as $document)
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50">
                        <div class="flex items-center space-x-3 min-w-0">
                            {{-- File type icon --}}
                            <div class="flex-shrink-0">
                                @if(str_contains($document->mime_type, 'pdf'))
                                    <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM6 20V4h7v5h5v11H6z"/></svg>
                                @elseif(str_contains($document->mime_type, 'image'))
                                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $document->label ?? $document->original_filename }}</p>
                                <p class="text-xs text-gray-500">{{ $document->original_filename }} &middot; {{ $document->formatted_size }} &middot; {{ $document->created_at->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <a href="{{ route('items.documents.download', [$item, $document]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Download</a>
                            <form method="POST" action="{{ route('items.documents.destroy', [$item, $document]) }}" onsubmit="return confirm('Delete this document?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No documents uploaded yet.</p>
        @endif
    </div>

    {{-- History Tab --}}
    <div x-show="tab === 'history'" class="p-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Change History</h3>
        @if($auditLogs->count())
            <div class="space-y-4">
                @foreach($auditLogs as $log)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                    @if($log->action === 'created') bg-green-100 text-green-800
                                    @elseif($log->action === 'updated') bg-blue-100 text-blue-800
                                    @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif
                                ">{{ ucfirst($log->action) }}</span>
                                <span class="text-sm text-gray-500">by {{ $log->user?->display_identifier ?? 'System' }}</span>
                            </div>
                            <span class="text-xs text-gray-400">{{ $log->created_at->format('M j, Y g:i A') }}</span>
                        </div>

                        @if($log->action === 'updated' && $log->old_values && $log->new_values)
                            <div class="mt-2 space-y-1">
                                @foreach($log->new_values as $field => $newValue)
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-700">{{ str_replace('_', ' ', ucfirst($field)) }}:</span>
                                        <span class="text-red-600 line-through">{{ is_array($log->old_values[$field] ?? '') ? json_encode($log->old_values[$field]) : ($log->old_values[$field] ?? 'empty') }}</span>
                                        <span class="text-gray-400 mx-1">&rarr;</span>
                                        <span class="text-green-700">{{ is_array($newValue) ? json_encode($newValue) : ($newValue ?: 'empty') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($log->action === 'created')
                            <p class="text-sm text-gray-500 mt-1">Item was created.</p>
                        @elseif($log->action === 'deleted')
                            <p class="text-sm text-gray-500 mt-1">Item was deleted.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-8">No change history recorded yet.</p>
        @endif
    </div>
</div>
@endsection
