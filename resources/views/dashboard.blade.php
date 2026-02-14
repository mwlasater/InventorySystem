@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h2>

{{-- Stat Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Total Items</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($totalItems) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Total Value</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">${{ number_format($totalValue, 2) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Cost Basis</p>
        <p class="text-3xl font-bold text-gray-800 mt-1">${{ number_format($totalCostBasis, 2) }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-sm font-medium text-gray-500">Gain / Loss</p>
        <p class="text-3xl font-bold mt-1 {{ $gainLoss >= 0 ? 'text-green-700' : 'text-red-700' }}">
            {{ $gainLoss >= 0 ? '+' : '' }}${{ number_format($gainLoss, 2) }}
        </p>
    </div>
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Items by Category</h3>
        <canvas id="categoryChart" height="250"></canvas>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Items by Status</h3>
        <canvas id="statusChart" height="250"></canvas>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    {{-- Recent Items --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-800">Recently Added</h3>
            <a href="{{ route('items.index', ['sort' => 'created_at', 'dir' => 'desc']) }}" class="text-blue-600 hover:text-blue-800 text-sm">View all</a>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($recentItems as $item)
                <a href="{{ route('items.show', $item) }}" class="flex items-center p-4 hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item->name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->category?->name }} &middot; {{ $item->created_at->diffForHumans() }}</p>
                    </div>
                    @if($item->estimated_value)
                        <span class="text-sm font-medium text-gray-600">${{ number_format($item->estimated_value, 2) }}</span>
                    @endif
                </a>
            @empty
                <p class="p-4 text-sm text-gray-500">No items yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Active Loans --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-800">Active Loans</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($loanedItems as $item)
                <a href="{{ route('items.show', $item) }}" class="flex items-center p-4 hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $item->name }}</p>
                        <p class="text-xs text-gray-500">{{ $item->location?->full_path }}</p>
                    </div>
                </a>
            @empty
                <p class="p-4 text-sm text-gray-500">No items currently loaned out.</p>
            @endforelse
            @if($overdueLoans->count())
                <div class="p-4 bg-orange-50">
                    <p class="text-sm font-medium text-orange-800">{{ $overdueLoans->count() }} overdue loan(s)</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-800 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-3">
        <a href="{{ route('items.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">Add Item</a>
        <a href="{{ route('items.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">Browse Inventory</a>
        <a href="{{ route('reports.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">Reports</a>
    </div>
</div>

<script type="module">
import Chart from 'chart.js/auto';

// Fetch chart data
fetch('{{ route("api.dashboard.charts") }}')
    .then(r => r.json())
    .then(data => {
        // Category doughnut chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: data.itemsByCategory.map(d => d.label),
                datasets: [{
                    data: data.itemsByCategory.map(d => d.value),
                    backgroundColor: ['#3B82F6','#EF4444','#10B981','#F59E0B','#6366F1','#EC4899','#8B5CF6','#14B8A6','#F97316','#64748B'],
                }],
            },
            options: { responsive: true, plugins: { legend: { position: 'right' } } },
        });
    });

// Status bar chart (from inline data)
const statusData = @json($itemsByStatus);
const statusLabels = @json(\App\Models\Item::STATUS_LABELS);
new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(statusData).map(k => statusLabels[k] || k),
        datasets: [{
            label: 'Items',
            data: Object.values(statusData),
            backgroundColor: '#3B82F6',
        }],
    },
    options: { responsive: true, plugins: { legend: { display: false } } },
});
</script>
@endsection
