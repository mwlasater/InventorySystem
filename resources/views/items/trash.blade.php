@extends('layouts.app')
@section('title', 'Trash')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('items.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Back to Inventory</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Trash</h2>
        <p class="text-gray-500 text-sm">Items are permanently deleted after 90 days.</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Deleted</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($items as $item)
                <tr>
                    <td class="px-6 py-4 text-gray-800">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->deleted_at?->diffForHumans() }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->deleted_at?->addDays(90)->diffForHumans() }}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <form method="POST" action="{{ route('trash.restore', $item) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-800 text-sm">Restore</button>
                        </form>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('trash.force-delete', $item) }}" class="inline" onsubmit="return confirm('Permanently delete? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete Forever</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">Trash is empty.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-3 border-t border-gray-200">{{ $items->links() }}</div>
</div>
@endsection
