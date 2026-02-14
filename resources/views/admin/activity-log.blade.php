@extends('layouts.app')
@section('title', 'Activity Log')

@section('content')
<div class="max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Activity Log</h2>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.activity-log') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
                <select name="user_id" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ ($filters['user_id'] ?? '') == $user->id ? 'selected' : '' }}>{{ $user->display_identifier }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
                <select name="action" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">All Actions</option>
                    <option value="created" {{ ($filters['action'] ?? '') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ ($filters['action'] ?? '') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ ($filters['action'] ?? '') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Entity Type</label>
                <input type="text" name="entity_type" value="{{ $filters['entity_type'] ?? '' }}" placeholder="e.g. Item" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">Filter</button>
                <a href="{{ route('admin.activity-log') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md text-sm">Clear</a>
            </div>
        </form>
    </div>

    {{-- Log Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Changes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-800">{{ $log->user?->display_identifier ?? 'System' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full
                                @if($log->action === 'created') bg-green-100 text-green-800
                                @elseif($log->action === 'updated') bg-blue-100 text-blue-800
                                @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif
                            ">{{ ucfirst($log->action) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-800">
                            {{ class_basename($log->entity_type ?? '') }} #{{ $log->entity_id }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            @if($log->action === 'updated' && $log->new_values)
                                <div x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded" class="text-blue-600 hover:text-blue-800 text-xs">
                                        {{ count($log->new_values) }} field(s) changed
                                        <span x-text="expanded ? '▲' : '▼'"></span>
                                    </button>
                                    <div x-show="expanded" x-cloak class="mt-1 space-y-1">
                                        @foreach($log->new_values as $field => $newValue)
                                            <div class="text-xs">
                                                <span class="font-medium">{{ str_replace('_', ' ', ucfirst($field)) }}:</span>
                                                <span class="text-red-600">{{ is_array($log->old_values[$field] ?? '') ? json_encode($log->old_values[$field]) : \Illuminate\Support\Str::limit($log->old_values[$field] ?? 'empty', 30) }}</span>
                                                &rarr;
                                                <span class="text-green-700">{{ is_array($newValue) ? json_encode($newValue) : \Illuminate\Support\Str::limit($newValue ?: 'empty', 30) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif($log->action === 'created')
                                <span class="text-xs text-green-600">New record</span>
                            @elseif($log->action === 'deleted')
                                <span class="text-xs text-red-600">Record removed</span>
                            @else
                                <span class="text-xs">{{ $log->action }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No activity found matching your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
