<!DOCTYPE html>
<html>
<head>
    <title>Purchase Requisition {{ $record->requisition_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Purchase Requisition</h1>
        <p>No: {{ $record->requisition_number }}</p>
    </div>

    <div class="details">
        <p><strong>Date:</strong> {{ $record->request_date->format('d/m/Y') }}</p>
        <p><strong>Requested By:</strong> {{ $record->requested_by }}</p>
        <p><strong>Department:</strong> {{ $record->department }}</p>
        <p><strong>Purpose:</strong> {{ $record->purpose }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ number_format($item['unit_price'], 2) }}</td>
                    <td>{{ number_format($item['quantity'] * $item['unit_price'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3">Grand Total</td>
                <td>{{ number_format($record->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 50px;">
        <p><strong>Remarks:</strong> {{ $record->remarks }}</p>
    </div>
</body>
</html>