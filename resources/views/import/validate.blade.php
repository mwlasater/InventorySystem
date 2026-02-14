@extends('layouts.app')
@section('title', 'Validate Import')

@section('content')
<div class="max-w-3xl mx-auto">
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
            <div class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="ml-2 text-sm text-green-600">Map Columns</span>
        </div>
        <div class="flex-1 h-0.5 bg-green-500 mx-4"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">3</div>
            <span class="ml-2 text-sm font-medium text-blue-600">Validate</span>
        </div>
        <div class="flex-1 h-0.5 bg-gray-300 mx-4"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-medium">4</div>
            <span class="ml-2 text-sm text-gray-500">Import</span>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Step 3: Validation Results</h3>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-800">{{ $results['total_count'] }}</p>
                <p class="text-sm text-gray-500">Total Rows</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $results['valid_count'] }}</p>
                <p class="text-sm text-green-600">Valid</p>
            </div>
            <div class="bg-{{ $results['error_count'] > 0 ? 'red' : 'gray' }}-50 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-{{ $results['error_count'] > 0 ? 'red' : 'gray' }}-600">{{ $results['error_count'] }}</p>
                <p class="text-sm text-{{ $results['error_count'] > 0 ? 'red' : 'gray' }}-600">Errors</p>
            </div>
        </div>

        {{-- Errors --}}
        @if(!empty($results['errors']))
            <div class="mb-6">
                <h4 class="text-sm font-medium text-red-600 mb-2">Validation Errors (rows with errors will be skipped during import):</h4>
                <div class="bg-red-50 border border-red-200 rounded-lg max-h-64 overflow-y-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-red-50">
                            <tr class="border-b border-red-200">
                                <th class="py-2 px-4 text-left text-xs font-medium text-red-700">Row</th>
                                <th class="py-2 px-4 text-left text-xs font-medium text-red-700">Field</th>
                                <th class="py-2 px-4 text-left text-xs font-medium text-red-700">Error</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-100">
                            @foreach(array_slice($results['errors'], 0, 50) as $error)
                                <tr>
                                    <td class="py-2 px-4 text-red-800">{{ $error['row'] }}</td>
                                    <td class="py-2 px-4 text-red-800">{{ $error['field'] }}</td>
                                    <td class="py-2 px-4 text-red-800">{{ $error['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(count($results['errors']) > 50)
                        <p class="text-sm text-red-600 px-4 py-2">... and {{ count($results['errors']) - 50 }} more errors</p>
                    @endif
                </div>
            </div>
        @endif

        @if($results['valid_count'] > 0)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-green-700">
                    <strong>{{ $results['valid_count'] }}</strong> items are ready to import.
                    @if($results['error_count'] > 0)
                        <strong>{{ $results['error_count'] }}</strong> rows with errors will be skipped.
                    @endif
                    New categories and locations will be created automatically if they don't exist.
                </p>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-700">No valid rows found. Please fix the errors in your CSV and try again.</p>
            </div>
        @endif
    </div>

    <div class="flex justify-between">
        <a href="{{ route('import.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2.5 px-6 rounded-md">Start Over</a>

        @if($results['valid_count'] > 0)
            <form method="POST" action="{{ route('import.execute') }}">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2.5 px-6 rounded-md"
                        onclick="this.disabled=true; this.innerText='Importing...'; this.form.submit();">
                    Import {{ $results['valid_count'] }} Items
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
