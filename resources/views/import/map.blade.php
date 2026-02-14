@extends('layouts.app')
@section('title', 'Map Columns')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Import Items from CSV</h2>
    </div>

    {{-- Step Indicator --}}
    <div class="flex items-center mb-8">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="ml-2 text-sm text-green-600">Upload</span>
        </div>
        <div class="flex-1 h-0.5 bg-green-500 mx-4"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">2</div>
            <span class="ml-2 text-sm font-medium text-blue-600">Map Columns</span>
        </div>
        <div class="flex-1 h-0.5 bg-gray-300 mx-4"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-medium">3</div>
            <span class="ml-2 text-sm text-gray-500">Validate</span>
        </div>
        <div class="flex-1 h-0.5 bg-gray-300 mx-4"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-medium">4</div>
            <span class="ml-2 text-sm text-gray-500">Import</span>
        </div>
    </div>

    <form method="POST" action="{{ route('import.validate') }}">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-800 mb-1">Step 2: Map CSV Columns to Item Fields</h3>
            <p class="text-sm text-gray-500 mb-4">Match each CSV column to the corresponding inventory field. Auto-suggestions have been applied where possible.</p>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">CSV Column</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Sample Data</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Map To</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($headers as $header)
                            <tr class="hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <span class="text-sm font-medium text-gray-800">{{ $header }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-gray-500">
                                        @if(!empty($preview))
                                            {{ \Illuminate\Support\Str::limit($preview[0][$header] ?? '', 40) }}
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <select name="mappings[{{ $header }}]" class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Skip this column --</option>
                                        @foreach($importableFields as $field => $label)
                                            <option value="{{ $field }}" {{ ($suggestions[$header] ?? '') === $field ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Preview Table --}}
        @if(!empty($preview))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-800 mb-3">Data Preview (first {{ count($preview) }} rows)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200">
                                @foreach($headers as $header)
                                    <th class="py-2 px-3 text-left text-xs font-medium text-gray-500 uppercase whitespace-nowrap">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($preview as $row)
                                <tr>
                                    @foreach($headers as $header)
                                        <td class="py-2 px-3 text-gray-600 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($row[$header] ?? '', 30) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="flex justify-between">
            <a href="{{ route('import.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 px-6 rounded-md">Back</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-6 rounded-md">Validate Data</button>
        </div>
    </form>
</div>
@endsection
