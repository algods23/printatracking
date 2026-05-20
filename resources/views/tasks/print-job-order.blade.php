<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Order — {{ $task->task_id }}</title>
    <style>
        @page {
            margin: 12mm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #111;
            line-height: 1.35;
        }
        .sheet {
            max-width: 190mm;
            margin: 0 auto;
            padding: 8px;
        }
        .no-print {
            margin-bottom: 16px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .no-print button,
        .no-print a {
            font-size: 13px;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #eab308;
            color: #111;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            display: inline-block;
        }
        .no-print a.secondary {
            background: #e5e7eb;
        }
        .header-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
        }
        .logo-block {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-shapes {
            display: flex;
            gap: 2px;
        }
        .tri {
            width: 0;
            height: 0;
            border-left: 10px solid transparent;
            border-right: 10px solid transparent;
        }
        .tri-cyan { border-bottom: 18px solid #06b6d4; }
        .tri-magenta { border-bottom: 18px solid #db2777; }
        .tri-yellow { border-bottom: 18px solid #eab308; }
        .logo-text-stack {
            font-size: 8px;
            font-weight: bold;
            line-height: 1.1;
            letter-spacing: 0.03em;
        }
        .company-title {
            text-align: right;
            flex: 1;
        }
        .company-title h1 {
            margin: 0 0 4px 0;
            font-size: 22px;
            letter-spacing: 0.04em;
            font-weight: 900;
        }
        .company-title p {
            margin: 0;
            font-size: 11px;
        }
        .customer-block {
            margin: 14px 0 10px;
        }
        .field-line {
            display: grid;
            grid-template-columns: 90px 1fr;
            align-items: end;
            margin-bottom: 6px;
        }
        .field-line.two-cols {
            grid-template-columns: 90px 1fr 80px 1fr;
            gap: 4px;
        }
        .field-label {
            font-weight: bold;
            white-space: nowrap;
            padding-right: 6px;
        }
        .field-value {
            border-bottom: 1px solid #000;
            min-height: 14px;
            padding: 2px 4px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 10px;
        }
        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 6px 5px;
        }
        table.items th {
            font-weight: bold;
            font-size: 10px;
            text-align: center;
        }
        table.items td.desc {
            text-align: left;
        }
        table.items td.n {
            text-align: center;
        }
        table.items td.money {
            text-align: right;
        }
        .cols-bottom {
            display: grid;
            grid-template-columns: 1fr 160px;
            gap: 12px;
            margin-top: 6px;
        }
        .left-col .row {
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .left-col strong {
            font-size: 10px;
        }
        .mode-label {
            font-weight: bold;
            margin-right: 8px;
        }
        .check-line {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-right: 14px;
        }
        .pay-box {
            display: inline-block;
            font-size: 15px;
            line-height: 1;
            vertical-align: middle;
            width: 1.1em;
            text-align: center;
            font-family: "Segoe UI Symbol", "Arial Unicode MS", "DejaVu Sans", Arial, sans-serif;
        }
        .job-lines {
            margin-top: 4px;
        }
        .job-lines .line {
            border-bottom: 1px solid #000;
            min-height: 22px;
            margin-top: 6px;
        }
        .totals-block {
            font-size: 11px;
        }
        .totals-block div {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            gap: 8px;
        }
        .totals-block span.label {
            font-weight: bold;
        }
        .footer-msg {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            letter-spacing: 0.06em;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            margin-top: 24px;
            font-size: 9px;
        }
        .footer-row .footnote {
            max-width: 55%;
            line-height: 1.3;
        }
        .processed {
            font-size: 10px;
        }
        .processed .sig-line {
            border-bottom: 1px solid #000;
            min-width: 140px;
            height: 20px;
            margin-top: 4px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sheet { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="no-print">
        <button type="button" onclick="window.print()">Print</button>
        <a href="{{ route('tasks.show', $task) }}" class="secondary">Back to task</a>
    </div>

    <div class="header-row">
        <div class="logo-block">
            @if(!empty($logoPath))
                <img src="{{ asset('storage/'.$logoPath) }}" alt="" style="max-height:48px;max-width:120px;">
            @else
                <div class="logo-shapes" aria-hidden="true">
                    <div class="tri tri-cyan"></div>
                    <div class="tri tri-magenta"></div>
                    <div class="tri tri-yellow"></div>
                </div>
                <div class="logo-text-stack">PRINTA<br>SIGNAGES &amp; STICKERS</div>
            @endif
        </div>
        <div class="company-title">
            <h1>{{ strtoupper($companyName) }}</h1>
            <p>{{ $companyAddress }}</p>
            <p>{{ $companyPhone }}</p>
        </div>
    </div>

    <div class="customer-block">
        <div class="field-line">
            <span class="field-label">Name :</span>
            <span class="field-value">{{ $task->customer_name }}</span>
        </div>
        <div class="field-line">
            <span class="field-label">Address :</span>
            <span class="field-value">&nbsp;</span>
        </div>
        <div class="field-line two-cols">
            <span class="field-label">Contact # :</span>
            <span class="field-value">{{ $task->contact_number }}</span>
            <span class="field-label">Date :</span>
            <span class="field-value">{{ now()->format('m/d/Y') }}</span>
        </div>
    </div>

    <p style="margin: 0 0 4px; font-size: 10px;"><strong>J.O./Task ID:</strong> {{ $task->task_id }}</p>

    <table class="items">
        <thead>
            <tr>
                <th style="width:48%">JOB ORDER</th>
                <th style="width:14%">QTY</th>
                <th style="width:18%">PRICE</th>
                <th style="width:20%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tableRows as $item)
                <tr>
                    <td class="desc">{{ $item ? $item->job_order : '' }}</td>
                    <td class="n">{{ $item ? $item->quantity : '' }}</td>
                    <td class="money">{{ $item ? '₱'.number_format((float)$item->price, 2) : '' }}</td>
                    <td class="money">{{ $item ? '₱'.number_format((float)$item->total, 2) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="cols-bottom">
        <div class="left-col">
            <div class="row">
                <span class="mode-label">MODE OF PAYMENT :</span>
                <span class="check-line">
                    <span class="pay-box">{!! $checkboxCash ? '&#9745;' : '&#9744;' !!}</span> CASH
                </span>
                <span class="check-line">
                    <span class="pay-box">{!! $checkboxGcash ? '&#9745;' : '&#9744;' !!}</span> GCASH
                </span>
            </div>
            <strong>JOB DETAILS :</strong>
            <div class="job-lines">
                <div class="line">{{ ($task->notes ?? '') !== '' ? \Illuminate\Support\Str::limit($task->notes, 500) : '' }}</div>
                <div class="line"></div>
            </div>
        </div>
        <div class="totals-block">
            <div>
                <span class="label">TOTAL :</span>
                <span>₱{{ number_format($totalAmount, 2) }}</span>
            </div>
            <div>
                <span class="label">DEPOSIT :</span>
                <span>₱{{ number_format($paidAmount, 2) }}</span>
            </div>
            <div>
                <span class="label">BALANCE :</span>
                <span>₱{{ number_format($balance, 2) }}</span>
            </div>
        </div>
    </div>

    <p class="footer-msg">WE ARE GRATEFUL TO SERVE YOU.</p>

    <div class="footer-row">
        <div class="footnote">NOT VALID FOR CLAIMING INPUT TAX. NOT AN OFFICIAL RECEIPT.</div>
        <div class="processed">
            PROCESSED BY:
            <div class="sig-line">{{ $printedBy }}</div>
        </div>
    </div>
</div>
</body>
</html>
