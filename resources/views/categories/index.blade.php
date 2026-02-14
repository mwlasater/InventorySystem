@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Categories</h2>
    <a href="{{ route('categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors text-sm">
        + Add Category
    </a>
</div>

<div class="bg-white rounded-lg shadow">
    @forelse($categories as $category)
        <div class="border-b border-gray-200 last:border-b-0">
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <span class="font-medium text-gray-800">{{ $category->name }}</span>
                    @if($category->description)
                        <span class="text-sm text-gray-500 ml-2">{{ $category->description }}</span>
                    @endif
                </div>
                <div class="flex items-center space-x-3 text-sm">
                    <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </div>
            @if($category->children->count())
                <div class="bg-gray-50 border-t border-gray-100">
                    @foreach($category->children as $sub)
                        <div class="flex items-center justify-between px-6 py-3 pl-12">
                            <span class="text-gray-700">{{ $sub->name }}</span>
                            <div class="flex items-center space-x-3 text-sm">
                                <a href="{{ route('categories.edit', $sub) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form method="POST" action="{{ route('categories.destroy', $sub) }}" class="inline" onsubmit="return confirm('Delete this subcategory?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="px-6 py-8 text-center text-gray-500">No categories yet. Create your first category to get started.</div>
    @endforelse
</div>
@endsection
