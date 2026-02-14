@extends('layouts.app')
@section('title', 'Export & Backup')

@section('content')
<div class="max-w-3xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Export & Backup</h2>

    <div class="grid gap-6">
        {{-- Full Inventory Export --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-1">Full Inventory Export</h3>
                    <p class="text-sm text-gray-500 mb-4">Download all active inventory items as a CSV file. Includes all item fields, category names, location paths, and tags. This export is compatible with the Import feature for data round-tripping.</p>
                </div>
                <svg class="w-10 h-10 text-blue-500 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <a href="{{ route('export.items') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download CSV
            </a>
        </div>

        {{-- Import Data --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-1">Import Items</h3>
                    <p class="text-sm text-gray-500 mb-4">Import inventory items from a CSV file. A wizard will guide you through column mapping, validation, and import. Categories, locations, and tags will be created automatically if they don't exist.</p>
                </div>
                <svg class="w-10 h-10 text-green-500 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <a href="{{ route('import.index') }}" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Start Import Wizard
            </a>
        </div>

        {{-- Reports --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-1">Reports</h3>
                    <p class="text-sm text-gray-500 mb-4">Generate and export detailed reports including collection summaries, valuation reports, and transaction history. Reports can be exported as CSV or PDF.</p>
                </div>
                <svg class="w-10 h-10 text-purple-500 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-md text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                View Reports
            </a>
        </div>

        {{-- Database Backup (Admin Only) --}}
        @if(auth()->user()->isAdmin())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-1">Database Backup</h3>
                        <p class="text-sm text-gray-500 mb-4">Database backups are managed via the command line. Run the following artisan command on your server to create a backup:</p>
                        <code class="block bg-gray-100 rounded-md px-4 py-2 text-sm text-gray-800 font-mono mb-2">php artisan backup:run</code>
                        <p class="text-xs text-gray-400">Add <code class="bg-gray-100 px-1 rounded">--with-media</code> to include uploaded photos and documents.</p>
                    </div>
                    <svg class="w-10 h-10 text-amber-500 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
