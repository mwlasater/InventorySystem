<div x-data="barcodeScanner()" x-cloak>
    {{-- Trigger button --}}
    <button @click="open = true" class="p-2 rounded-md hover:bg-gray-100 text-gray-600" title="Scan Barcode/QR">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
    </button>

    {{-- Scanner Modal --}}
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.outside="close()">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium">Scan Barcode / QR Code</h3>
                <button @click="close()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                <div id="barcode-reader" class="w-full"></div>

                {{-- Manual entry --}}
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Or enter barcode manually:</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="manualBarcode" @keydown.enter="lookupBarcode(manualBarcode)" class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter barcode...">
                        <button @click="lookupBarcode(manualBarcode)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">Lookup</button>
                    </div>
                </div>

                {{-- Result --}}
                <div x-show="result" class="mt-4 p-3 rounded-md" :class="result?.found ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200'">
                    <template x-if="result?.found">
                        <div>
                            <p class="text-sm font-medium text-green-800">Item found!</p>
                            <p class="text-sm text-green-700" x-text="result.item.name"></p>
                            <a :href="result.item.url" class="text-sm text-blue-600 hover:text-blue-800 font-medium mt-1 inline-block">View Item &rarr;</a>
                        </div>
                    </template>
                    <template x-if="result && !result.found">
                        <div>
                            <p class="text-sm font-medium text-yellow-800">No item found for this barcode.</p>
                            <a :href="result.create_url" class="text-sm text-blue-600 hover:text-blue-800 font-medium mt-1 inline-block">Create new item with this barcode &rarr;</a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function barcodeScanner() {
    return {
        open: false,
        scanner: null,
        manualBarcode: '',
        result: null,

        async close() {
            if (this.scanner) {
                try { await this.scanner.stop(); } catch(e) {}
                this.scanner = null;
            }
            this.open = false;
            this.result = null;
            this.manualBarcode = '';
        },

        async startScanner() {
            if (typeof Html5Qrcode === 'undefined') return;

            this.scanner = new Html5Qrcode('barcode-reader');
            try {
                await this.scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    (decodedText) => this.onScanSuccess(decodedText),
                    () => {}
                );
            } catch (err) {
                console.log('Camera not available, use manual entry.');
            }
        },

        async onScanSuccess(decodedText) {
            if (this.scanner) {
                try { await this.scanner.stop(); } catch(e) {}
            }

            // If it's a URL to our app, navigate directly
            if (decodedText.startsWith(window.location.origin)) {
                window.location.href = decodedText;
                return;
            }

            this.lookupBarcode(decodedText);
        },

        async lookupBarcode(barcode) {
            if (!barcode) return;
            try {
                const response = await fetch('/api/barcode/lookup?barcode=' + encodeURIComponent(barcode));
                this.result = await response.json();
            } catch (err) {
                this.result = { found: false, create_url: '/items/create?barcode=' + encodeURIComponent(barcode) };
            }
        },

        init() {
            this.$watch('open', (val) => {
                if (val) this.$nextTick(() => this.startScanner());
            });
        }
    };
}
</script>
