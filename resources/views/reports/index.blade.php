@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Reports</h2>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <a href="{{ route('reports.collection-summary') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-medium text-gray-800 mb-2">Collection Summary</h3>
        <p class="text-sm text-gray-500">Counts and values by category.</p>
    </a>
    <a href="{{ route('reports.valuation') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-medium text-gray-800 mb-2">Valuation Report</h3>
        <p class="text-sm text-gray-500">Purchase price vs. estimated value.</p>
    </a>
    <a href="{{ route('reports.transactions') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-medium text-gray-800 mb-2">Transaction Report</h3>
        <p class="text-sm text-gray-500">Sales and disposition activity.</p>
    </a>
    <a href="{{ route('reports.location-inventory') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-medium text-gray-800 mb-2">Location Inventory</h3>
        <p class="text-sm text-gray-500">Items by location hierarchy.</p>
    </a>
    <a href="{{ route('reports.status-breakdown') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-medium text-gray-800 mb-2">Status Breakdown</h3>
        <p class="text-sm text-gray-500">Item counts by status.</p>
    </a>
    <a href="{{ route('reports.acquisition-history') }}" class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow">
        <h3 class="text-lg font-medium text-gray-800 mb-2">Acquisition History</h3>
        <p class="text-sm text-gray-500">Items by acquisition date.</p>
    </a>
</div>
@endsection
