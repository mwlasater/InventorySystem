@extends('layouts.app')
@section('title', 'Add Item')

@section('content')
<div class="max-w-4xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Add New Item</h2>

    @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <p class="text-yellow-800 font-medium">{{ session('warning') }}</p>
            @if(session('duplicates'))
                <ul class="mt-2 text-sm text-yellow-700">
                    @foreach(session('duplicates') as $dup)
                        <li><a href="{{ route('items.show', $dup) }}" class="underline" target="_blank">{{ $dup->name }}</a> ({{ $dup->sku ?? $dup->barcode ?? 'similar name' }})</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('items.store') }}" class="space-y-6">
        @csrf
        @if(session('duplicates'))
            <input type="hidden" name="skip_duplicate_check" value="1">
        @endif

        {{-- Basic Information --}}
        <div class="bg-white rounded-lg shadow p-6" x-data="{ open: true }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                <h3 class="text-lg font-semibold text-gray-800">Basic Information</h3>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Item Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category_id" id="category_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        <option value="">Select category...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @foreach($cat->children as $sub)
                                <option value="{{ $sub->id }}" {{ old('category_id') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;{{ $sub->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity', 1) }}" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="condition_rating" class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                    <select name="condition_rating" id="condition_rating" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        <option value="">Not rated</option>
                        @foreach(\App\Models\Item::CONDITION_LABELS as $val => $label)
                            <option value="{{ $val }}" {{ old('condition_rating') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" id="status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        <option value="in_collection" {{ old('status', 'in_collection') == 'in_collection' ? 'selected' : '' }}>In Collection</option>
                        <option value="damaged" {{ old('status') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>
                </div>
                <div>
                    <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand / Manufacturer</label>
                    <input type="text" name="brand" id="brand" value="{{ old('brand') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="model_number" class="block text-sm font-medium text-gray-700 mb-1">Model / Part Number</label>
                    <input type="text" name="model_number" id="model_number" value="{{ old('model_number') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU / Item Code</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">Barcode / QR Code</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                    <input type="text" name="color" id="color" value="{{ old('color') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="dimensions" class="block text-sm font-medium text-gray-700 mb-1">Dimensions</label>
                    <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="year_manufactured" class="block text-sm font-medium text-gray-700 mb-1">Year Manufactured</label>
                    <input type="text" name="year_manufactured" id="year_manufactured" value="{{ old('year_manufactured') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Location --}}
        <div class="bg-white rounded-lg shadow p-6" x-data="{ open: true }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                <h3 class="text-lg font-semibold text-gray-800">Location</h3>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-4">
                <label for="location_id" class="block text-sm font-medium text-gray-700 mb-1">Location *</label>
                <select name="location_id" id="location_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="">Select location...</option>
                    @foreach($locations as $building)
                        <option value="{{ $building->id }}" {{ old('location_id') == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                        @foreach($building->children as $room)
                            <option value="{{ $room->id }}" {{ old('location_id') == $room->id ? 'selected' : '' }}>&nbsp;&nbsp;{{ $room->name }}</option>
                            @foreach($room->children as $unit)
                                <option value="{{ $unit->id }}" {{ old('location_id') == $unit->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;&nbsp;{{ $unit->name }}</option>
                                @foreach($unit->children as $shelf)
                                    <option value="{{ $shelf->id }}" {{ old('location_id') == $shelf->id ? 'selected' : '' }}>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $shelf->name }}</option>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                </select>
                @error('location_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Acquisition & Valuation --}}
        <div class="bg-white rounded-lg shadow p-6" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                <h3 class="text-lg font-semibold text-gray-800">Acquisition & Valuation</h3>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="acquisition_date" class="block text-sm font-medium text-gray-700 mb-1">Acquisition Date</label>
                    <input type="date" name="acquisition_date" id="acquisition_date" value="{{ old('acquisition_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="acquisition_method" class="block text-sm font-medium text-gray-700 mb-1">Acquisition Method</label>
                    <select name="acquisition_method" id="acquisition_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                        <option value="">Not specified</option>
                        @foreach(\App\Models\Item::ACQUISITION_METHODS as $val => $label)
                            <option value="{{ $val }}" {{ old('acquisition_method') == $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="acquisition_source" class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                    <input type="text" name="acquisition_source" id="acquisition_source" value="{{ old('acquisition_source') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700 mb-1">Purchase Price</label>
                    <input type="number" name="purchase_price" id="purchase_price" value="{{ old('purchase_price') }}" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="estimated_value" class="block text-sm font-medium text-gray-700 mb-1">Estimated Value</label>
                    <input type="number" name="estimated_value" id="estimated_value" value="{{ old('estimated_value') }}" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="valuation_date" class="block text-sm font-medium text-gray-700 mb-1">Valuation Date</label>
                    <input type="date" name="valuation_date" id="valuation_date" value="{{ old('valuation_date') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label for="valuation_source" class="block text-sm font-medium text-gray-700 mb-1">Valuation Source</label>
                    <input type="text" name="valuation_source" id="valuation_source" value="{{ old('valuation_source') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Notes & Tags --}}
        <div class="bg-white rounded-lg shadow p-6" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                <h3 class="text-lg font-semibold text-gray-800">Notes & Tags</h3>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse class="mt-4 space-y-4">
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags (comma separated)</label>
                    <input type="text" name="tags_input" id="tags_input" value="{{ old('tags_input') }}" placeholder="vintage, rare, electronics" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        onchange="this.value.split(',').forEach(t => { if(t.trim()) { let i = document.createElement('input'); i.type='hidden'; i.name='tags[]'; i.value=t.trim(); this.form.appendChild(i); }})">
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_favorite" value="1" {{ old('is_favorite') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Mark as Favorite</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition-colors">Create Item</button>
            <a href="{{ route('items.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-md transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
