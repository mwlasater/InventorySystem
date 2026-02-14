@extends('layouts.guest')
@section('title', 'Change Password')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Change Your Password</h2>
    <p class="text-sm text-gray-600 mb-6">You must change your password before continuing.</p>

    <form method="POST" action="{{ route('password.force-change.update') }}">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input type="password" name="password" id="password" required autofocus
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500">Min 8 chars, with uppercase, lowercase, digit, and special character.</p>
        </div>

        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
            Change Password
        </button>
    </form>
</div>
@endsection
