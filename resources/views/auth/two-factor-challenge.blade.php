@extends('layouts.guest')
@section('title', 'Two-Factor Authentication')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8" x-data="{ recovery: false }">
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Two-Factor Authentication</h2>

    <p class="text-sm text-gray-600 mb-6" x-show="!recovery">
        Enter the 6-digit code from your authenticator app.
    </p>
    <p class="text-sm text-gray-600 mb-6" x-show="recovery" x-cloak>
        Enter one of your recovery codes.
    </p>

    <form method="POST" action="{{ route('two-factor.challenge.store') }}">
        @csrf

        <div x-show="!recovery">
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Authentication code</label>
            <input type="text" name="code" id="code" inputmode="numeric" autocomplete="one-time-code"
                placeholder="123456" x-bind:disabled="recovery" autofocus
                class="w-full px-3 py-2 border border-gray-300 rounded-md tracking-widest text-center focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
        </div>

        <div x-show="recovery" x-cloak>
            <label for="recovery_code" class="block text-sm font-medium text-gray-700 mb-1">Recovery code</label>
            <input type="text" name="recovery_code" id="recovery_code" autocomplete="one-time-code"
                placeholder="XXXXX-XXXXX" x-bind:disabled="!recovery"
                class="w-full px-3 py-2 border border-gray-300 rounded-md text-center focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        @error('code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <button type="submit" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
            Verify
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        <button type="button" @click="recovery = !recovery" class="text-blue-600 hover:text-blue-800">
            <span x-show="!recovery">Use a recovery code</span>
            <span x-show="recovery" x-cloak>Use an authenticator code</span>
        </button>
    </p>
</div>
@endsection
