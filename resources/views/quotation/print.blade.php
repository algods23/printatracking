<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing</title>
    <style>
        @page { margin: 14px; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .page {
            width: 100%;
        }
        .brand-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
        }
        .brand-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }
        .logo {
            width: 70px;
            height: 52px;
            object-fit: contain;
            display: block;
        }
        .brand-text h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: 0.01em;
        }
        .brand-text .line {
            margin-top: 2px;
            font-size: 10px;
            line-height: 1.2;
        }
        .billing-number {
            font-size: 12px;
            font-weight: 700;
            text-align: right;
            padding-top: 6px;
            white-space: nowrap;
        }
        .client-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin: 4px 0 10px;
            font-size: 10px;
        }
        .client-box {
            max-width: 58%;
        }
        .client-label {
            display: block;
            font-size: 10px;
        }
        .client-name {
            font-size: 13px;
            font-weight: 700;
            margin-top: 1px;
        }
        .client-subtext {
            color: #4b5563;
            font-size: 9px;
            margin-top: 1px;
        }
        .statement-title {
            text-align: center;
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 0.35em;
            color: #c1121f;
            margin: 8px 0 12px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .table th,
        .table td {
            border: 1px solid #1f2937;
            padding: 7px 6px;
            vertical-align: top;
        }
        .table th {
            background: #1d9ed8;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: center;
        }
        .table td {
            font-size: 9px;
            line-height: 1.2;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .details { word-break: break-word; }
        .muted { color: #6b7280; }
        .empty-row td { text-align: center; padding: 16px 8px; }
        .total-row {
            display: flex;
            justify-content: flex-end;
            align-items: baseline;
            gap: 10px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: 800;
        }
        .total-row .label {
            letter-spacing: 0.02em;
        }
        .total-row .value {
            min-width: 120px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="brand-row">
            <div class="brand-left">
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Logo" class="logo">
                @endif
                <div class="brand-text">
                    <h1>{{ $companyName }}</h1>
                    <div class="line">{{ $companyAddress }}</div>
                    <div class="line">{{ $companyPhone }}</div>
                </div>
            </div>
            <div class="billing-number">Billing Statement #{{ $billingReference }}</div>
        </div>

        <div class="client-row">
            <div class="client-box">
                <span class="client-label">Client Name :</span>
                <div class="client-name">{{ $customerName ?: 'Multiple Customers' }}</div>
                @if($customerContact)
                    <div class="client-subtext">{{ $customerContact }}</div>
                @endif
            </div>
        </div>

        <div class="statement-title">BILLING STATEMENT</div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 16%">Date</th>
                    <th style="width: 17%">Jo #</th>
                    <th style="width: 9%">Qty</th>
                    <th style="width: 10%">Unit</th>
                    <th style="width: 28%">Details</th>
                    <th style="width: 10%">Price</th>
                    <th style="width: 10%">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($billingRows as $row)
                    <tr>
                        <td class="center">{{ $row['date'] }}</td>
                        <td>{{ $row['job_order'] }}</td>
                        <td class="center">{{ $row['quantity'] }}</td>
                        <td class="center">{{ $row['unit'] }}</td>
                        <td class="details">{{ $row['details'] }}</td>
                        <td class="right">{{ number_format($row['price'], 2) }}</td>
                        <td class="right">{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7">No billing data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="total-row">
            <div class="label">TOTAL :</div>
            <div class="value">{{ number_format($totalAmount, 2) }}</div>
        </div>
    </div>
</body>
</html>