@extends('layouts.app')
@section('title', 'Tags')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Tags</h2>
    <p class="text-gray-600 mt-1">Tags are created inline when adding or editing items.</p>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tag</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($tags as $tag)
                <tr x-data="{ editing: false }">
                    <td class="px-6 py-3">
                        <span x-show="!editing" class="text-gray-800">{{ $tag->name }}</span>
                        <form x-show="editing" method="POST" action="{{ route('tags.update', $tag) }}" class="flex items-center space-x-2">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" value="{{ $tag->name }}" class="px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500">
                            <button type="submit" class="text-green-600 text-sm">Save</button>
                            <button type="button" @click="editing = false" class="text-gray-600 text-sm">Cancel</button>
                        </form>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">{{ $tag->items_count }}</td>
                    <td class="px-6 py-3 text-right text-sm space-x-2">
                        <button @click="editing = true" x-show="!editing" class="text-blue-600 hover:text-blue-800">Rename</button>
                        <form method="POST" action="{{ route('tags.destroy', $tag) }}" class="inline" onsubmit="return confirm('Delete this tag?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-4 text-center text-gray-500">No tags yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-6 py-3 border-t border-gray-200">
        {{ $tags->links() }}
    </div>
</div>
@endsection
