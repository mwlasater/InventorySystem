@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="max-w-3xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Settings</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">{{ session('success') }}</div>
    @endif

    {{-- Editable application settings --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Application</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="app_display_name" class="block text-sm font-medium text-gray-700 mb-1">Application name</label>
                <input type="text" name="app_display_name" id="app_display_name"
                    value="{{ old('app_display_name', $settings['app_display_name']) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('app_display_name') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Shown in the header and page titles.</p>
                @error('app_display_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label for="items_per_page" class="block text-sm font-medium text-gray-700 mb-1">Items per page</label>
                <select name="items_per_page" id="items_per_page"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach([25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) old('items_per_page', $settings['items_per_page']) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Default page size for the inventory list.</p>
            </div>

            <div class="mb-4">
                <label for="trash_retention_days" class="block text-sm font-medium text-gray-700 mb-1">Trash retention (days)</label>
                <input type="number" name="trash_retention_days" id="trash_retention_days" min="1" max="3650"
                    value="{{ old('trash_retention_days', $settings['trash_retention_days']) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('trash_retention_days') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">Trashed items are permanently purged after this many days.</p>
                @error('trash_retention_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="enforce_2fa_for_admins" value="0">
                    <input type="checkbox" name="enforce_2fa_for_admins" value="1" @checked(old('enforce_2fa_for_admins', $settings['enforce_2fa_for_admins']))
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Require admins to set up two-factor authentication</span>
                </label>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                Save settings
            </button>
        </form>
    </div>

    {{-- Read-only system overview --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">System</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Environment</dt>
                <dd class="text-gray-800 font-medium">{{ $system['environment'] }}@if($system['debug']) <span class="text-amber-600">(debug)</span>@endif</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Laravel / PHP</dt>
                <dd class="text-gray-800 font-medium">{{ $system['laravel_version'] }} / {{ $system['php_version'] }}</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Database</dt>
                <dd class="text-gray-800 font-medium">{{ $system['database'] }}</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Queue</dt>
                <dd class="text-gray-800 font-medium">{{ $system['queue'] }}</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Mail</dt>
                <dd class="font-medium {{ $system['mail_configured'] ? 'text-green-700' : 'text-amber-600' }}">
                    {{ $system['mail_configured'] ? 'configured ('.$system['mail_mailer'].')' : 'not configured ('.$system['mail_mailer'].')' }}
                </dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Active items</dt>
                <dd class="text-gray-800 font-medium">{{ number_format($system['item_count']) }}</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Users</dt>
                <dd class="text-gray-800 font-medium">{{ number_format($system['user_count']) }}</dd>
            </div>
            <div class="flex justify-between border-b border-gray-100 pb-2">
                <dt class="text-gray-500">Backups</dt>
                <dd class="text-gray-800 font-medium">{{ $system['backup_count'] }}@if($system['latest_backup_at']) · latest {{ $system['latest_backup_at'] }}@endif</dd>
            </div>
        </dl>
    </div>
</div>
@endsection
