<!DOCTYPE html>
<html>
<head>
    <title>Valuation Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .date { font-size: 10px; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .positive { color: green; }
        .negative { color: red; }
    </style>
</head>
<body>
    <h1>Valuation Report</h1>
    <p class="date">Generated: {{ now()->format('M j, Y g:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Category</th>
                <th class="text-right">Purchase Price</th>
                <th class="text-right">Estimated Value</th>
                <th class="text-right">Gain/Loss</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['category'] ?? '' }}</td>
                    <td class="text-right">{{ $item['purchase_price'] ? '$'.number_format($item['purchase_price'], 2) : '' }}</td>
                    <td class="text-right">${{ number_format($item['estimated_value'], 2) }}</td>
                    <td class="text-right {{ ($item['gain_loss'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        {{ $item['gain_loss'] !== null ? ($item['gain_loss'] >= 0 ? '+' : '') . '$' . number_format($item['gain_loss'], 2) : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
