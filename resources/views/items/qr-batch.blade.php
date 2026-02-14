@extends('layouts.app')
@section('title', 'Batch Print QR Labels')

@section('content')
<div class="mb-6 flex justify-between items-center no-print">
    <h2 class="text-2xl font-bold text-gray-800">Batch Print QR Labels ({{ $labels->count() }} items)</h2>
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md text-sm">Print All</button>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="printable-labels">
    @foreach($labels as $label)
        <div class="bg-white rounded-lg shadow p-4 text-center print-label">
            <div class="flex justify-center mb-2">
                {!! $label['qrCode'] !!}
            </div>
            <h4 class="text-sm font-bold text-gray-800 truncate">{{ $label['item']->name }}</h4>
            @if($label['item']->sku)
                <p class="text-xs text-gray-600">{{ $label['item']->sku }}</p>
            @endif
            @if($label['item']->location)
                <p class="text-xs text-gray-500 truncate">{{ $label['item']->location->full_path }}</p>
            @endif
        </div>
    @endforeach
</div>

<style media="print">
    .no-print { display: none !important; }
    #printable-labels { display: grid !important; grid-template-columns: repeat(3, 1fr); gap: 10px; }
    .print-label { break-inside: avoid; border: 1px solid #ddd; padding: 12px; }
</style>
@endsection
