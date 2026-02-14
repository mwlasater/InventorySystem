@extends('layouts.app')
@section('title', 'Valuation Report')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Reports</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-1">Valuation Report</h2>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.valuation', ['format' => 'csv']) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm">Export CSV</a>
        <a href="{{ route('reports.valuation', ['format' => 'pdf']) }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">Export PDF</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Purchase Price</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Estimated Value</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Gain/Loss</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $item['name'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item['category'] ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ $item['purchase_price'] ? '$'.number_format($item['purchase_price'], 2) : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">${{ number_format($item['estimated_value'], 2) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-medium {{ ($item['gain_loss'] ?? 0) >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        {{ $item['gain_loss'] !== null ? ($item['gain_loss'] >= 0 ? '+' : '') . '$' . number_format($item['gain_loss'], 2) : '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
