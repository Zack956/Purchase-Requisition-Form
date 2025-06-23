<!DOCTYPE html>
<html>
<head>
    <title>Purchase Requisition - {{ $record->requisition_number }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .logo-container {
            margin-right: 20px;
        }
        .logo {
            height: 80px;
        }
        .company-info {
            text-align: center;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .company-co {
            font-size: 14px;
            margin-top: -5px;
        }
        .document-title {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .requisition-no {
            font-size: 16px;
            color: #555;
        }
        .details {
            margin: 25px 0;
        }
        .detail-row {
            margin-bottom: 8px;
            display: flex;
        }
        .detail-label {
            width: 120px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 10px;
            border: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .grand-total {
            text-align: right;
            font-size: 18px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 40px;
        }
        .signature {
            margin-top: 80px;
        }
        .signature-line {
            width: 250px;
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="header">
        <img class="logo" src="{{ storage_path('app/public/Logo.PNG') }}" alt="Company Logo">
        <div class="company-info">
            <div class="company-name">SYNZTEC (MALAYSIA) SDN. BHD.</div>
            <div class="company-co">co so:</div>
            <div class="document-title">PURCHASE REQUISITION</div>
        </div>
    <div class="details">
        <div class="detail-row">
            <span class="detail-label">Date:</span>
            <span>{{ $record->request_date->format('d/m/Y') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Requested By:</span>
            <span>{{ $record->requested_by }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Department:</span>
            <span>{{ $record->department }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Purpose:</span>
            <span>{{ $record->purpose }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price (MYR)</th>
                <th>Total (MYR)</th>
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
            <tr class="total-row">
                <td colspan="3">Grand Total:</td>
                <td>{{ number_format($record->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="detail-row">
            <span class="detail-label">Remarks:</span>
            <span>{{ $record->remarks ?? 'N/A' }}</span>
        </div>
        
        <div class="signature">
            <div class="signature-line"></div>
            <div>Approved By: _________________________</div>
            <div style="margin-top: 30px;">
                <div class="signature-line"></div>
                <div>Received By: _________________________</div>
            </div>
        </div>
    </div>
</body>
</html>