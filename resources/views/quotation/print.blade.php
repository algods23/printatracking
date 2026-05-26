<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing Statement</title>
    <style>
        @page { margin: 12px; size: A4; }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #1a1a1a;
            font-size: 10px;
            line-height: 1.3;
        }

        .page {
            width: 100%;
            max-width: 100%;
            background: white;
            padding: 0;
        }

        /* ── COMPANY HEADER ── */
        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #d4af37;
            padding: 10px 16px;
            gap: 16px;
            min-height: 80px;
        }

        .header-logo {
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }

        .header-logo img {
            max-height: 100px;
            max-width: 160px;
            object-fit: contain;
        }

        .header-company {
            flex: 1;
            text-align: right;
        }

        .header-company .name {
            font-size: 16px;
            font-weight: 900;
            color: #000;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-company .address {
            font-size: 9.5px;
            color: #222;
            margin-top: 2px;
        }

        /* ── BILLING STATEMENT TITLE ── */
        .title-banner {
            background: #d4af37;
            text-align: center;
            padding: 10px;
            border-top: 1px solid #b8960c;
        }

        .title-banner h1 {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: 0.15em;
            color: #000;
            margin: 0;
        }

        /* ── BILL TO / STATEMENT DETAILS ── */
        .meta-row {
            display: flex;
            gap: 12px;
            padding: 12px;
            align-items: stretch;
        }

        .bill-to-box, .statement-details-box {
            border: 1px solid #999;
            padding: 0;
            flex: 1;
            background: white;
        }

        .box-header {
            background: #d4af37;
            color: #000;
            font-weight: 700;
            font-size: 10px;
            padding: 6px 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #999;
        }

        .box-content {
            padding: 10px;
            font-size: 9px;
            line-height: 1.5;
        }

        .bill-to-box .client-name {
            font-weight: 700;
            margin-bottom: 2px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .details-table tr td {
            padding: 2px 0;
            vertical-align: top;
        }

        .details-table tr td:first-child {
            font-weight: 600;
            white-space: nowrap;
            min-width: 90px;
        }

        .details-table tr td:nth-child(2) {
            padding: 0 6px;
            color: #666;
        }

        /* ── PRODUCT TABLE ── */
        .product-section {
            padding: 0 12px;
            margin-top: 12px;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 0;
        }

        .product-table thead tr th {
            background: #d4af37;
            color: #000;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #999;
            text-align: left;
        }

        .product-table tbody tr td {
            border: 1px solid #999;
            padding: 8px 6px;
            vertical-align: top;
        }

        .product-table tbody tr:nth-child(even) td {
            background: #faf8f3;
        }

        .product-desc {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .product-specs {
            list-style: none;
            padding-left: 12px;
            font-size: 8px;
            color: #555;
        }

        .product-specs li {
            position: relative;
            padding-left: 8px;
            margin-bottom: 2px;
        }

        .product-specs li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #d4af37;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── TOTALS ── */
        .totals-section {
            padding: 0 12px;
            margin-top: 4px;
            margin-bottom: 4px;
        }

        .totals-table {
            width: 280px;
            border-collapse: collapse;
            font-size: 10px;
            margin-left: auto;
            margin-right: 0;
        }

        .totals-table tr td {
            border: 1px solid #999;
            padding: 6px 8px;
        }

        .totals-table tr td:last-child {
            text-align: right;
            font-weight: 600;
        }

        .totals-table tr.subtotal td {
            background: #faf8f3;
        }

        .totals-table tr.total-due td {
            background: #d4af37;
            font-weight: 700;
            color: #000;
        }

        /* ── PAYMENT SECTION ── */
        .payment-section {
            padding: 0 12px;
            margin-top: 8px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .payment-instructions {
            width: 240px;
            flex-shrink: 0;
            border: 1px dashed #999;
            padding: 10px;
            background: #fffdf0;
            font-size: 9px;
            line-height: 1.4;
        }

        .pi-title {
            font-weight: 700;
            color: #b8860b;
            font-size: 10px;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .pi-name {
            font-weight: 700;
            margin: 4px 0 6px 0;
        }

        /* ── NOTE BANNER ── */
        .note-banner {
            background: #d4af37;
            padding: 8px 12px;
            font-size: 9px;
            font-weight: 600;
            margin: 12px 12px 0;
            border: 1px solid #999;
        }

        /* ── FOOTER ── */
        .footer-section {
            padding: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 12px;
        }

        .footer-left {
            font-size: 10px;
            line-height: 1.5;
            text-align: left;
        }

        .footer-left .company-name {
            font-style: italic;
            color: #d4af37;
            font-weight: 600;
        }

        .footer-right {
            text-align: right;
            font-size: 9px;
            margin-right: 40px;
        }

        .sig-name {
            font-weight: 600;
            margin-top: 40px;
            text-decoration: underline;
        }

        .sig-title {
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="page">

        <!-- HEADER: LOGO LEFT | COMPANY DETAILS RIGHT -->
        <div class="header-top">
            <div class="header-logo">
                <img src="{{ $logoDataUri }}" alt="Logo">
            </div>
            <div class="header-company">
                <div class="name">Printa Signages &amp; Stickers</div>
                <div class="address">Kumintang St., Mintal, Davao City</div>
                <div class="address">09667550044</div>
            </div>
        </div>

        <!-- BILLING STATEMENT TITLE -->
        <div class="title-banner">
            <h1>BILLING STATEMENT</h1>
        </div>

        <!-- BILL TO / STATEMENT DETAILS -->
        <div class="meta-row">
            <div class="bill-to-box">
                <div class="box-header">Bill To:</div>
                <div class="box-content">
                    <div class="client-name">{{ $customerName ?: 'Multiple Customers' }}</div>
                    @if($customerContact)
                        <div>{{ $customerContact }}</div>
                    @endif
                </div>
            </div>
            <div class="statement-details-box">
                <div class="box-header">Statement Details</div>
                <div class="box-content">
                    <table class="details-table">
                        <tr>
                            <td>Statement No.</td>
                            <td>:</td>
                            <td>BS-{{ now()->format('Y-m-d') }}-{{ $billingReference }}</td>
                        </tr>
                        <tr>
                            <td>Date</td>
                            <td>:</td>
                            <td>{{ now()->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Payment Terms</td>
                            <td>:</td>
                            <td>50% Downpayment, 50% Upon Receipt</td>
                        </tr>
                        <tr>
                            <td>Due Date</td>
                            <td>:</td>
                            <td>{{ $dueDate ?? now()->addDays(14)->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- PRODUCT TABLE -->
        <div class="product-section">
            <table class="product-table">
                <thead>
                    <tr>
                        <th style="width: 12%">Date</th>
                        <th style="width: 36%">Product</th>
                        <th style="width: 14%">Unit Price</th>
                        <th style="width: 10%">Qty</th>
                        <th style="width: 28%">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billingRows as $row)
                        <tr>
                            <td class="text-center" style="vertical-align: middle;">
                                {{ $row['date'] ?? '' }}
                            </td>
                            <td>
                                <div class="product-desc">{{ $row['details'] }}</div>
                                @if(!empty($row['specs']))
                                    <ul class="product-specs">
                                        @foreach($row['specs'] as $spec)
                                            <li>{{ $spec }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($row['price'], 2) }}</td>
                            <td class="text-center">{{ $row['quantity'] }}</td>
                            <td class="text-right">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 20px; color: #999;">No billing data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- SUBTOTAL & TOTAL -->
        <div class="totals-section">
            <table class="totals-table">
                <tr class="subtotal">
                    <td>Subtotal</td>
                    <td>{{ number_format($totalAmount, 2) }}</td>
                </tr>
                <tr class="subtotal">
                    <td>Deposit</td>
                    <td>{{ number_format($totalDeposit, 2) }}</td>
                </tr>
                <tr class="subtotal">
                    <td>Balance</td>
                    <td>{{ number_format($totalBalance, 2) }}</td>
                </tr>
                <tr class="total-due">
                    <td>TOTAL AMOUNT DUE</td>
                    <td>{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- PAYMENT INSTRUCTIONS -->
        <div class="payment-section">
            <div class="payment-instructions">
                <div class="pi-title">Payment Instructions</div>
                <div>Please make check payable to:</div>
                <div class="pi-name">KRISTINE PANTASTICO</div>
                <div>Kindly send proof of payment for verification. Thank you!</div>
            </div>
        </div>

        <!-- NOTE BANNER -->
        <div class="note-banner">
            <strong>NOTE:</strong> Must deposit 50% of the total amount of orders, and remaining 50% upon receipt of items.
        </div>

        <!-- FOOTER -->
        <div class="footer-section">
            <div class="footer-left">
                Thank you for choosing<br>
                <span class="company-name">Printa Signages &amp; Stickers</span>
            </div>
            <div class="footer-right">
                <div>Signature:</div>
                <div class="sig-name">{{ $authRep ?? 'Jelian Fernandez' }}</div>
                <div class="sig-title">Authorized Representative</div>
            </div>
        </div>

    </div>
</body>
</html>