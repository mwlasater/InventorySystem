@extends('layouts.app')
@section('title', 'Acquisition History Report')

@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Reports</a>
    <h2 class="text-2xl font-bold text-gray-800 mt-1">Acquisition History</h2>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border-gray-300 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-md border-gray-300 text-sm">
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Filter</button>
    </form>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $item->acquisition_date?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm"><a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:text-blue-800">{{ $item->name }}</a></td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->category?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->acquisition_method_label ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $item->acquisition_source ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ $item->purchase_price ? '$'.number_format($item->purchase_price, 2) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-8 text-sm text-gray-500 text-center">No items found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
