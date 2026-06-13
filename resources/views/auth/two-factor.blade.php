@extends('layouts.app')
@section('title', 'Two-Factor Authentication')

@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Two-Factor Authentication</h2>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        @if($user->hasTwoFactorEnabled())
            {{-- Enabled --}}
            <div class="flex items-center mb-4">
                <span class="inline-block px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Enabled</span>
                <p class="ml-3 text-sm text-gray-600">Your account is protected by an authenticator app.</p>
            </div>

            <h3 class="text-lg font-semibold text-gray-800 mb-2">Recovery codes</h3>
            <p class="text-sm text-gray-600 mb-3">
                Store these somewhere safe. Each code can be used once if you lose access to your authenticator.
            </p>
            <ul class="grid grid-cols-2 gap-2 font-mono text-sm bg-gray-50 border border-gray-200 rounded-md p-4 mb-4">
                @foreach($recoveryCodes ?? [] as $code)
                    <li>{{ $code }}</li>
                @endforeach
            </ul>

            <div class="flex gap-3">
                <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                    @csrf
                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                        Regenerate recovery codes
                    </button>
                </form>
            </div>

            <hr class="my-6">

            <h3 class="text-lg font-semibold text-gray-800 mb-2">Disable</h3>
            <form method="POST" action="{{ route('two-factor.disable') }}" class="max-w-sm">
                @csrf
                @method('DELETE')
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Confirm your password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="mt-3 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    Disable two-factor authentication
                </button>
            </form>

        @elseif($qrSvg)
            {{-- Enrolling: scan + confirm --}}
            <h3 class="text-lg font-semibold text-gray-800 mb-2">1. Scan this QR code</h3>
            <p class="text-sm text-gray-600 mb-3">Open your authenticator app (Google Authenticator, Authy, 1Password, …) and scan:</p>
            <div class="inline-block bg-white border border-gray-200 rounded-md p-3 mb-2">{!! $qrSvg !!}</div>

            <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-2">2. Confirm a code</h3>
            <form method="POST" action="{{ route('two-factor.confirm') }}" class="max-w-xs">
                @csrf
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required autofocus
                    placeholder="123456"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md tracking-widest text-center focus:outline-none focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
                @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button type="submit" class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    Confirm &amp; enable
                </button>
            </form>

            <p class="mt-3 text-xs text-gray-500 max-w-xs">
                Changed your mind? Just leave this page — two-factor isn't active until you confirm a code.
            </p>

        @else
            {{-- Not enrolled --}}
            <p class="text-sm text-gray-600 mb-4">
                Add a second step to your login using an authenticator app. After entering your password,
                you'll be asked for a 6-digit code.
            </p>
            <form method="POST" action="{{ route('two-factor.enable') }}">
                @csrf
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                    Enable two-factor authentication
                </button>
            </form>
        @endif
    </div>

    <p class="mt-4 text-sm">
        <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:text-blue-800">&larr; Back to profile</a>
    </p>
</div>
@endsection
