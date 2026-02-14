@extends('layouts.app')
@section('title', 'Locations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Locations</h2>
    <a href="{{ route('locations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors text-sm">
        + Add Location
    </a>
</div>

<div class="bg-white rounded-lg shadow" x-data="{ expanded: {} }">
    @forelse($locations as $building)
        <div class="border-b border-gray-200 last:border-b-0">
            <div class="flex items-center justify-between px-6 py-4 cursor-pointer hover:bg-gray-50" @click="expanded[{{ $building->id }}] = !expanded[{{ $building->id }}]">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2 transition-transform" :class="{ 'rotate-90': expanded[{{ $building->id }}] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="font-medium text-gray-800">{{ $building->name }}</span>
                    <span class="ml-2 text-xs text-gray-400 uppercase">{{ $building->level }}</span>
                </div>
                <div class="flex items-center space-x-3 text-sm" @click.stop>
                    <a href="{{ route('locations.create', ['parent_id' => $building->id]) }}" class="text-green-600 hover:text-green-800">+ Child</a>
                    <a href="{{ route('locations.edit', $building) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                    <form method="POST" action="{{ route('locations.destroy', $building) }}" class="inline" onsubmit="return confirm('Delete this location and all children?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </div>
            </div>

            <div x-show="expanded[{{ $building->id }}]" x-collapse>
                @foreach($building->children as $room)
                    <div class="border-t border-gray-100">
                        <div class="flex items-center justify-between px-6 py-3 pl-12 hover:bg-gray-50 cursor-pointer" @click="expanded['r{{ $room->id }}'] = !expanded['r{{ $room->id }}']">
                            <div class="flex items-center">
                                @if($room->children->count())
                                    <svg class="w-4 h-4 text-gray-400 mr-2 transition-transform" :class="{ 'rotate-90': expanded['r{{ $room->id }}'] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                @else
                                    <span class="w-4 mr-2"></span>
                                @endif
                                <span class="text-gray-700">{{ $room->name }}</span>
                                <span class="ml-2 text-xs text-gray-400 uppercase">{{ $room->level }}</span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm" @click.stop>
                                <a href="{{ route('locations.create', ['parent_id' => $room->id]) }}" class="text-green-600 hover:text-green-800">+ Child</a>
                                <a href="{{ route('locations.edit', $room) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                <form method="POST" action="{{ route('locations.destroy', $room) }}" class="inline" onsubmit="return confirm('Delete?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                </form>
                            </div>
                        </div>

                        <div x-show="expanded['r{{ $room->id }}']" x-collapse>
                            @foreach($room->children as $unit)
                                <div class="border-t border-gray-50">
                                    <div class="flex items-center justify-between px-6 py-2 pl-20 hover:bg-gray-50 cursor-pointer" @click="expanded['u{{ $unit->id }}'] = !expanded['u{{ $unit->id }}']">
                                        <div class="flex items-center">
                                            @if($unit->children->count())
                                                <svg class="w-4 h-4 text-gray-400 mr-2 transition-transform" :class="{ 'rotate-90': expanded['u{{ $unit->id }}'] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            @else
                                                <span class="w-4 mr-2"></span>
                                            @endif
                                            <span class="text-gray-600">{{ $unit->name }}</span>
                                            <span class="ml-2 text-xs text-gray-400 uppercase">{{ $unit->level }}</span>
                                        </div>
                                        <div class="flex items-center space-x-3 text-sm" @click.stop>
                                            <a href="{{ route('locations.create', ['parent_id' => $unit->id]) }}" class="text-green-600 hover:text-green-800">+ Child</a>
                                            <a href="{{ route('locations.edit', $unit) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                        </div>
                                    </div>

                                    <div x-show="expanded['u{{ $unit->id }}']" x-collapse>
                                        @foreach($unit->children as $shelf)
                                            <div class="flex items-center justify-between px-6 py-2 pl-28 hover:bg-gray-50 border-t border-gray-50">
                                                <div class="flex items-center">
                                                    <span class="text-gray-500">{{ $shelf->name }}</span>
                                                    <span class="ml-2 text-xs text-gray-400 uppercase">{{ $shelf->level }}</span>
                                                </div>
                                                <div class="flex items-center space-x-3 text-sm">
                                                    <a href="{{ route('locations.edit', $shelf) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="px-6 py-8 text-center text-gray-500">No locations yet. Add your first building to get started.</div>
    @endforelse
</div>
@endsection
