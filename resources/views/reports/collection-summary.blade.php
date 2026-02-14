@extends('layouts.app')
@section('title', 'Collection Summary Report')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Reports</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-1">Collection Summary</h2>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('reports.collection-summary', ['format' => 'csv']) }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm">Export CSV</a>
        <a href="{{ route('reports.collection-summary', ['format' => 'pdf']) }}" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-md text-sm">Export PDF</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Value</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Cost</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($categories as $cat)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $cat['name'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ $cat['count'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">${{ number_format($cat['total_value'], 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">${{ number_format($cat['total_cost'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50">
            <tr>
                <td class="px-6 py-3 text-sm font-medium text-gray-800">Total</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-800 text-right">{{ $categories->sum('count') }}</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-800 text-right">${{ number_format($categories->sum('total_value'), 2) }}</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-800 text-right">${{ number_format($categories->sum('total_cost'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
