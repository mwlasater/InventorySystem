@extends('layouts.app')
@section('title', 'Create Location')

@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Create Location
        @if($parent)
            <span class="text-lg text-gray-500 font-normal">under {{ $parent->full_path }}</span>
        @endif
    </h2>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('locations.store') }}">
            @csrf
            @if($parent)
                <input type="hidden" name="parent_id" value="{{ $parent->id }}">
            @endif
            <input type="hidden" name="level" value="{{ $nextLevel }}">

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Location Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($nextLevel) }}</span>
            </div>

            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                <textarea name="description" id="description" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">Create Location</button>
                <a href="{{ route('locations.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
