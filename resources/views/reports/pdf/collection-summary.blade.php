<!DOCTYPE html>
<html>
<head>
    <title>Collection Summary Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 18px; margin-bottom: 5px; }
        .date { font-size: 10px; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f5f5f5; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        tfoot td { font-weight: bold; background-color: #f5f5f5; }
    </style>
</head>
<body>
    <h1>Collection Summary Report</h1>
    <p class="date">Generated: {{ now()->format('M j, Y g:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total Value</th>
                <th class="text-right">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat['name'] }}</td>
                    <td class="text-right">{{ $cat['count'] }}</td>
                    <td class="text-right">${{ number_format($cat['total_value'], 2) }}</td>
                    <td class="text-right">${{ number_format($cat['total_cost'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="text-right">{{ $categories->sum('count') }}</td>
                <td class="text-right">${{ number_format($categories->sum('total_value'), 2) }}</td>
                <td class="text-right">${{ number_format($categories->sum('total_cost'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
