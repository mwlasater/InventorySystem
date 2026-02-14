@extends('layouts.app')
@section('title', 'Location Inventory Report')

@section('content')
<div class="mb-6">
    <a href="{{ route('reports.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Reports</a>
    <h2 class="text-2xl font-bold text-gray-800 mt-1">Location Inventory</h2>
</div>

<div class="space-y-4">
    @forelse($locations as $location)
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-medium text-gray-800">{{ $location->name }} <span class="text-sm text-gray-500">({{ $location->items_count }} items)</span></h3>
            @if($location->items->count())
                <ul class="mt-2 ml-4 text-sm text-gray-600 list-disc">
                    @foreach($location->items->take(5) as $item)
                        <li><a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:text-blue-800">{{ $item->name }}</a></li>
                    @endforeach
                    @if($location->items->count() > 5)
                        <li class="text-gray-400">...and {{ $location->items->count() - 5 }} more</li>
                    @endif
                </ul>
            @endif
        </div>
    @empty
        <p class="text-gray-500 text-sm">No locations found.</p>
    @endforelse
</div>
@endsection
