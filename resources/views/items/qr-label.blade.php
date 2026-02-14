@extends('layouts.app')
@section('title', 'QR Label - ' . $item->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Back to {{ $item->name }}</a>
</div>

<div class="max-w-md mx-auto">
    <div class="bg-white rounded-lg shadow p-8 text-center" id="printable-label">
        <div class="flex justify-center mb-4">
            {!! $qrCode !!}
        </div>
        <h3 class="text-lg font-bold text-gray-800">{{ $item->name }}</h3>
        @if($item->sku)
            <p class="text-sm text-gray-600">SKU: {{ $item->sku }}</p>
        @endif
        @if($item->barcode)
            <p class="text-sm text-gray-600">Barcode: {{ $item->barcode }}</p>
        @endif
        @if($item->location)
            <p class="text-xs text-gray-500 mt-1">{{ $item->location->full_path }}</p>
        @endif
    </div>

    <div class="flex justify-center mt-6 space-x-3">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md text-sm">Print Label</button>
    </div>
</div>

<style media="print">
    body * { visibility: hidden; }
    #printable-label, #printable-label * { visibility: visible; }
    #printable-label { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); }
</style>
@endsection
