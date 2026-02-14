@extends('layouts.app')
@section('title', 'Import Items')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Import Items from CSV</h2>
    </div>

    {{-- Step Indicator --}}
    <div class="flex items-center mb-8">
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-medium">1</div>
            <span class="ml-2 text-sm font-medium text-blue-600">Upload</span>
        </div>
        <div class="flex-1 h-0.5 bg-gray-300 mx-4"></div>
        <div class="flex items-center">
            <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center text-sm font-medium">2</div>
            <span class="ml-2 text-sm text-gray-500">Map Columns</span>
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

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-800 mb-4">Step 1: Upload CSV File</h3>

        <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-4" x-data="{ fileName: '' }">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">Select CSV File</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-400 transition-colors">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-gray-600 mb-2" x-show="!fileName">Drag and drop your CSV file here, or click to browse</p>
                    <p class="text-sm text-blue-600 font-medium" x-show="fileName" x-text="fileName"></p>
                    <input type="file" name="file" id="file" accept=".csv,.txt" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="fileName = $event.target.files[0]?.name || ''" style="position:relative">
                </div>
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Requirements:</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>- CSV format with headers in the first row</li>
                    <li>- Maximum file size: 10 MB</li>
                    <li>- At minimum, a "Name" column is required</li>
                    <li>- Columns will be auto-mapped in the next step</li>
                </ul>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-md">
                Upload & Continue
            </button>
        </form>
    </div>
</div>
@endsection
