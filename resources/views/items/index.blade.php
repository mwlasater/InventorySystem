@extends('layouts.app')
@section('title', 'Inventory')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Inventory</h2>
    <div class="flex items-center space-x-3">
        {{-- View Toggle --}}
        <div class="flex bg-gray-200 rounded-lg p-1">
            <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}" class="px-3 py-1 rounded text-sm {{ $viewMode === 'grid' ? 'bg-white shadow text-gray-800' : 'text-gray-600' }}">Grid</a>
            <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}" class="px-3 py-1 rounded text-sm {{ $viewMode === 'list' ? 'bg-white shadow text-gray-800' : 'text-gray-600' }}">List</a>
        </div>
        <a href="{{ route('items.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors text-sm">+ Add Item</a>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-lg shadow p-4 mb-6" x-data="{ showFilters: false }">
    <div class="flex items-center justify-between">
        <button @click="showFilters = !showFilters" class="text-sm text-gray-600 hover:text-gray-800 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filters
        </button>
        <div class="text-sm text-gray-500">{{ $items->total() }} items</div>
    </div>
    <form method="GET" action="{{ route('items.index') }}" x-show="showFilters" x-collapse class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
        <select name="category_id" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @foreach($cat->children as $sub)
                    <option value="{{ $sub->id }}" {{ request('category_id') == $sub->id ? 'selected' : '' }}>&nbsp;&nbsp;{{ $sub->name }}</option>
                @endforeach
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            <option value="">All Statuses</option>
            @foreach(\App\Models\Item::STATUS_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="sort" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Date Added</option>
            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
            <option value="estimated_value" {{ request('sort') == 'estimated_value' ? 'selected' : '' }}>Value</option>
            <option value="purchase_price" {{ request('sort') == 'purchase_price' ? 'selected' : '' }}>Price</option>
        </select>
        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Apply</button>
            <a href="{{ route('items.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm">Clear</a>
        </div>
    </form>
</div>

{{-- Bulk Action Bar --}}
<div x-data="{ selected: [], showBulk: false }" x-init="$watch('selected', val => showBulk = val.length > 0)">
    <div x-show="showBulk" x-transition class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 flex items-center justify-between">
        <span class="text-sm text-blue-800" x-text="selected.length + ' items selected'"></span>
        <div class="flex space-x-2">
            <form method="POST" action="{{ route('items.bulk') }}">
                @csrf
                <template x-for="id in selected"><input type="hidden" name="item_ids[]" :value="id"></template>
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm" onclick="return confirm('Move selected items to trash?')">Delete</button>
            </form>
        </div>
    </div>

    @if($viewMode === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($items as $item)
                <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow overflow-hidden">
                    <a href="{{ route('items.show', $item) }}" class="block">
                        <div class="h-48 bg-gray-200 flex items-center justify-center">
                            @if($item->primary_photo)
                                <img src="{{ asset('storage/' . $item->primary_photo->thumbnail_md) }}" alt="{{ $item->name }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                    </a>
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <a href="{{ route('items.show', $item) }}" class="font-medium text-gray-800 hover:text-blue-600 line-clamp-1">{{ $item->name }}</a>
                            <input type="checkbox" :value="{{ $item->id }}" x-model="selected" class="rounded border-gray-300 text-blue-600 ml-2 mt-1">
                        </div>
                        <p class="text-sm text-gray-500 mt-1">{{ $item->category?->name ?? 'Uncategorized' }}</p>
                        <div class="flex items-center justify-between mt-2">
                            @if($item->estimated_value)
                                <span class="text-sm font-semibold text-green-700">${{ number_format($item->estimated_value, 2) }}</span>
                            @else
                                <span class="text-sm text-gray-400">No value</span>
                            @endif
                            <span class="text-xs px-2 py-1 rounded-full {{ $item->status === 'in_collection' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $item->status_label }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    <p>No items found.</p>
                    <a href="{{ route('items.create') }}" class="mt-2 inline-block text-blue-600 hover:text-blue-800">Add your first item</a>
                </div>
            @endforelse
        </div>
    @else
        {{-- List View --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 w-8"></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><input type="checkbox" :value="{{ $item->id }}" x-model="selected" class="rounded border-gray-300 text-blue-600"></td>
                            <td class="px-4 py-3">
                                <a href="{{ route('items.show', $item) }}" class="font-medium text-gray-800 hover:text-blue-600">{{ $item->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $item->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $item->location?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $item->estimated_value ? '$'.number_format($item->estimated_value, 2) : '-' }}</td>
                            <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full {{ $item->status === 'in_collection' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $item->status_label }}</span></td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $item->quantity }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4">{{ $items->links() }}</div>
</div>
@endsection
