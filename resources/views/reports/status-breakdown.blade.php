@extends('layouts.app')
@section('title', 'Status Breakdown Report')

@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Reports</a>
    <h2 class="text-2xl font-bold text-gray-800 mt-1">Status Breakdown</h2>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Count</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Value</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($statuses as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-800">{{ $s['status'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">{{ $s['count'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-800 text-right">${{ number_format($s['total_value'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
