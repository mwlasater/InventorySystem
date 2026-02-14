@extends('layouts.app')
@section('title', 'Transaction Report')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Reports</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-1">Transaction Report</h2>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.transactions', array_merge(request()->query(), ['format' => 'csv'])) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm">Export CSV</a>
    </div>
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

<div class="bg-blue-50 rounded-lg p-4 mb-4">
    <p class="text-sm text-blue-800"><strong>Total Net Proceeds:</strong> ${{ number_format($totalProceeds, 2) }}</p>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Net Proceeds</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($transactions as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $t->transaction_date->format('M j, Y') }}</td>
                    <td class="px-6 py-4 text-sm"><a href="{{ route('items.show', $t->item_id) }}" class="text-blue-600 hover:text-blue-800">{{ $t->item?->name }}</a></td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $t->type_label }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ $t->net_proceeds ? '$'.number_format($t->net_proceeds, 2) : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $t->recipient_name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-6 py-8 text-sm text-gray-500 text-center">No transactions found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
