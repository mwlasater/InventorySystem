@extends('layouts.app')
@section('title', 'Record Transaction - ' . $item->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('items.show', $item) }}" class="text-blue-600 hover:text-blue-800 text-sm">&larr; Back to {{ $item->name }}</a>
</div>

<div class="max-w-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Record Transaction</h2>
    <p class="text-gray-600 mb-6">Item: <strong>{{ $item->name }}</strong> &mdash; Current status: <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $item->status === 'in_collection' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $item->status_label }}</span></p>

    <form method="POST" action="{{ route('items.transactions.store', $item) }}" class="bg-white rounded-lg shadow p-6 space-y-6" x-data="transactionForm()">
        @csrf

        {{-- Transaction Type --}}
        <div>
            <label for="transaction_type" class="block text-sm font-medium text-gray-700 mb-1">Transaction Type <span class="text-red-500">*</span></label>
            <select name="transaction_type" id="transaction_type" x-model="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                <option value="">Select type...</option>
                @foreach($transactionTypes as $value => $label)
                    <option value="{{ $value }}" {{ ($preselectedType ?? old('transaction_type')) === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('transaction_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Transaction Date --}}
        <div>
            <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
            <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            @error('transaction_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Recipient (shown for sold, given_away, traded, loaned_out) --}}
        <div x-show="showRecipient" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="recipient_name" class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
                    <input type="text" name="recipient_name" id="recipient_name" value="{{ old('recipient_name') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="recipient_contact" class="block text-sm font-medium text-gray-700 mb-1">Recipient Contact</label>
                    <input type="text" name="recipient_contact" id="recipient_contact" value="{{ old('recipient_contact') }}" placeholder="Email, phone, etc." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Sale details (shown for sold, traded) --}}
        <div x-show="showSaleDetails" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">Sale Price ($)</label>
                    <input type="number" name="sale_price" id="sale_price" value="{{ old('sale_price') }}" step="0.01" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="shipping_cost" class="block text-sm font-medium text-gray-700 mb-1">Shipping Cost ($)</label>
                    <input type="number" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost') }}" step="0.01" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                    <input type="text" name="platform" id="platform" value="{{ old('platform') }}" placeholder="eBay, Facebook, etc." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Expected return date (shown for loaned_out) --}}
        <div x-show="type === 'loaned_out'" x-cloak>
            <label for="expected_return_date" class="block text-sm font-medium text-gray-700 mb-1">Expected Return Date</label>
            <input type="date" name="expected_return_date" id="expected_return_date" value="{{ old('expected_return_date') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @error('expected_return_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('notes') }}</textarea>
        </div>

        {{-- Status change preview --}}
        <div x-show="type" x-cloak class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <p class="text-sm text-blue-800">
                <strong>Status will change:</strong>
                {{ $item->status_label }} &rarr; <span x-text="statusPreview"></span>
            </p>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('items.show', $item) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md text-sm">Cancel</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md text-sm">Record Transaction</button>
        </div>
    </form>
</div>

<script>
function transactionForm() {
    return {
        type: '{{ $preselectedType ?? old('transaction_type', '') }}',
        get showRecipient() {
            return ['sold', 'given_away', 'traded', 'loaned_out'].includes(this.type);
        },
        get showSaleDetails() {
            return ['sold', 'traded'].includes(this.type);
        },
        get statusPreview() {
            const map = {
                sold: 'Sold',
                given_away: 'Given Away',
                traded: 'Traded',
                loaned_out: 'Loaned Out',
                returned: 'In Collection',
                lost: 'Lost',
                disposed: 'Disposed',
                status_correction: 'In Collection',
            };
            return map[this.type] || '—';
        },
    };
}
</script>
@endsection
