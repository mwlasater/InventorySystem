@extends('layouts.app')
@section('title', 'User Activity')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Back to Users</a>
    <h2 class="text-2xl font-bold text-gray-800 mt-2">Activity Log: {{ $user->display_identifier }}</h2>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($log->action === 'create') bg-green-100 text-green-800
                            @elseif($log->action === 'update') bg-blue-100 text-blue-800
                            @elseif($log->action === 'delete') bg-red-100 text-red-800
                            @elseif($log->action === 'login') bg-purple-100 text-purple-800
                            @elseif($log->action === 'failed_login') bg-orange-100 text-orange-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->entity_type ? ucfirst($log->entity_type) . ' #' . $log->entity_id : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->ip_address ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No activity recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $logs->links() }}
    </div>
</div>
@endsection
