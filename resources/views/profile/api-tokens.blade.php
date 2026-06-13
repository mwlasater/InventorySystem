@extends('layouts.app')
@section('title', 'API Tokens')

@section('content')
<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">API Tokens</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md text-sm">{{ session('success') }}</div>
    @endif

    {{-- Plaintext token is shown exactly once, right after creation. --}}
    @if(session('plain_text_token'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-md">
            <p class="text-sm font-medium text-amber-800 mb-2">
                Copy your new token for "{{ session('token_name') }}" now — it won't be shown again.
            </p>
            <code class="block w-full break-all bg-white border border-amber-200 rounded p-3 text-sm text-gray-800">{{ session('plain_text_token') }}</code>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Create a token</h3>
        <p class="text-sm text-gray-600 mb-4">
            Tokens authenticate API requests on your behalf and carry your account's access.
            Send it as a <code>Authorization: Bearer &lt;token&gt;</code> header.
        </p>
        <form method="POST" action="{{ route('api-tokens.store') }}" class="flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Token name</label>
                <input type="text" name="name" id="name" required placeholder="e.g. Mobile scanner"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                Create
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Your tokens</h3>
        @if($tokens->isEmpty())
            <p class="text-sm text-gray-500">You haven't created any API tokens yet.</p>
        @else
            <div class="divide-y divide-gray-200 border border-gray-200 rounded-lg">
                @foreach($tokens as $token)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-800">{{ $token->name }}</p>
                            <p class="text-xs text-gray-500">
                                Created {{ $token->created_at->diffForHumans() }} ·
                                Last used {{ $token->last_used_at?->diffForHumans() ?? 'never' }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('api-tokens.destroy', $token->id) }}"
                            onsubmit="return confirm('Revoke this token? Any client using it will stop working.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Revoke</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <p class="mt-4 text-sm">
        <a href="{{ route('profile.edit') }}" class="text-blue-600 hover:text-blue-800">&larr; Back to profile</a>
    </p>
</div>
@endsection
