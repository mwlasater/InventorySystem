@extends('layouts.guest')
@section('title', 'Forgot Password')

@section('content')
<div class="bg-white rounded-lg shadow-md p-8">
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Forgot Password</h2>
    <p class="text-sm text-gray-600 mb-6">Enter your email address and we'll send you a password reset link.</p>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
            Send Reset Link
        </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800">Back to login</a>
    </p>
</div>
@endsection
