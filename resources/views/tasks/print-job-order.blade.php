<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Order — {{ $task->task_id }}</title>
    <style>
        @page {
            margin: 0.3in;
            size: 8.5in 11in;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #111;
            line-height: 1.2;
            display: flex;
            flex-wrap: wrap;
            gap: 0.2in;
        }
        .sheet {
            width: 3.85in;
            max-width: 100%;
            margin: 0;
            padding: 0.15in;
            border: 1px dashed #999;
            flex: 0 0 auto;
        }
        .sheet:last-child {
            margin-bottom: 0;
        }
        .copy-label {
            text-align: right;
            font-size: 8px;
            font-weight: bold;
            color: #666;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }
        .no-print {
            margin-bottom: 8px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .sheet:nth-child(2) .no-print {
            display: none !important;
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
            gap: 4px;
            margin-bottom: 4px;
        }
        .logo-block {
            display: flex;
            align-items: center;
        }
        .fallback-logo {
            position: relative;
            width: 0.8in;
            min-width: 0.8in;
            height: 0.25in;
        }
        .logo-shapes {
            position: absolute;
            top: 0;
            left: 0.12in;
            display: flex;
            align-items: flex-start;
            gap: 0.015in;
        }
        .logo-tile {
            width: 0.1in;
            height: 0.1in;
            transform: rotate(45deg);
            border-radius: 0.015in;
        }
        .tile-cyan {
            background: #06b6d4;
            margin-top: 0.045in;
        }
        .tile-magenta {
            background: #be3455;
        }
        .tile-yellow {
            background: #eab308;
            margin-top: 0.045in;
        }
        .logo-word {
            position: absolute;
            left: 0;
            bottom: 0.09in;
            font-size: 0.12in;
            line-height: 1;
            font-weight: 900;
            font-style: italic;
            letter-spacing: 0;
        }
        .logo-subtitle {
            position: absolute;
            left: 0.02in;
            bottom: 0;
            font-size: 0.055in;
            line-height: 1;
            font-weight: 900;
            letter-spacing: 0;
            white-space: nowrap;
        }
        .company-title {
            text-align: center;
            flex: 1;
        }
        .company-title h1 {
            margin: 0 0 1px 0;
            font-size: 13px;
            letter-spacing: 0.04em;
            font-weight: 900;
        }
        .company-title p {
            margin: 0;
            font-size: 9px;
        }
        .customer-block {
            margin: 3px 0 2px;
        }
        .field-line {
            display: grid;
            grid-template-columns: 65px 1fr;
            align-items: end;
            margin-bottom: 2px;
        }
        .field-line.two-cols {
            grid-template-columns: 65px 1fr;
            gap: 0;
            margin-bottom: 2px;
        }
        .field-label {
            font-weight: bold;
            white-space: nowrap;
            padding-right: 4px;
            font-size: 9px;
        }
        .field-value {
            border-bottom: 1px solid #000;
            min-height: 9px;
            padding: 0px 1px;
            font-size: 9px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 3px;
            font-size: 8px;
        }
        table.items th,
        table.items td {
            border: 1px solid #000; 
            padding: 1px 2px;
            font-size: 8px;
        }
        table.items th {
            font-weight: bold;
            font-size: 8px;
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
            display: block;
            margin-top: 3px;
        }
        .left-col {
            margin-bottom: 3px;
        }
        .left-col .row {
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 2px;
            font-size: 8px;
        }
        .left-col strong {
            font-size: 8px;
            display: block;
            margin-top: 2px;
        }
        .mode-label {
            font-weight: bold;
            margin-right: 4px;
        }
        .check-line {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            margin-right: 8px;
        }
        .pay-box {
            display: inline-block;
            font-size: 10px;
            line-height: 1;
            vertical-align: middle;
            width: 1.1em;
            text-align: center;
            font-family: "Segoe UI Symbol", "Arial Unicode MS", "DejaVu Sans", Arial, sans-serif;
        }
        .job-lines {
            margin-top: 2px;
        }
        .job-lines .line {
            border-bottom: 1px solid #000;
            min-height: 12px;
            margin-top: 2px;
            font-size: 9px;
        }
        .totals-block {
            font-size: 9px;
            font-weight: 900;
            line-height: 1.1;
            padding-top: 1px;
            border-top: 1px solid #000;
            padding-top: 2px;
            margin-top: 2px;
        }
        .totals-block div {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            border-bottom: 0;
            padding: 0;
            gap: 4px;
            margin-bottom: 1px;
        }
        .totals-block span.label {
            font-weight: bold;
            letter-spacing: 0;
        }
        .totals-block span.amount {
            text-align: right;
            font-size: 8px;
            font-weight: 700;
        }
        .footer-msg {
            text-align: center;
            margin-top: 3px;
            margin-bottom: 2px;
            font-weight: bold;
            letter-spacing: 0.04em;
            font-size: 8px;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
            font-size: 7px;
        }
        .footer-row .footnote {
            max-width: 55%;
            line-height: 1.1;
        }
        .processed {
            font-size: 7px;
        }
        .processed .sig-line {
            border-bottom: 1px solid #000;
            min-width: 0.7in;
            height: 8px;
            margin-top: 1px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; display: flex; flex-wrap: wrap; gap: 0.2in; }
            .sheet { padding: 0.15in; max-width: none; width: 3.85in; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <div class="copy-label">COMPANY COPY</div>
    <div class="no-print">
        <button type="button" onclick="window.print()">Print</button>
        <button type="button" onclick="saveSingleCopy()">Save as PDF</button>
        <a href="{{ route('tasks.show', $task) }}" class="secondary">Back to task</a>
    </div>

    <div class="header-row">
        <div class="logo-block">
            @if(!empty($logoPath))
                <img src="{{ asset('storage/'.$logoPath) }}" alt="" style="max-height:0.24in;max-width:0.7in;">
            @else
                <div class="fallback-logo" aria-label="Printa Signages and Stickers">
                    <div class="logo-shapes" aria-hidden="true">
                        <span class="logo-tile tile-cyan"></span>
                        <span class="logo-tile tile-magenta"></span>
                        <span class="logo-tile tile-yellow"></span>
                    </div>
                    <div class="logo-word">PRINTA</div>
                    <div class="logo-subtitle">SIGNAGES &amp; STICKERS</div>
                </div>
            @endif
        </div>
        <div class="company-title">
            <h1>{{ strtoupper($companyName) }}</h1>
            <p>{{ $companyAddress }}</p>
            <p>{{ $companyPhone }}</p>
        </div>
    </div>
<p style="margin: 0 0 8px; font-size: 12px; text-align: right;"><strong>J.O./Task ID:</strong> {{ $task->task_id }}</p>
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
                <span class="amount">₱{{ number_format($totalAmount, 2) }}</span>
            </div>
            <div>
                <span class="label">DEPOSIT :</span>
                <span class="amount">₱{{ number_format($paidAmount, 2) }}</span>
            </div>
            <div>
                <span class="label">BALANCE :</span>
                <span class="amount">₱{{ number_format($balance, 2) }}</span>
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

<!-- CUSTOMER COPY -->
<div class="sheet">
    <div class="copy-label">CUSTOMER COPY</div>

    <div class="header-row">
        <div class="logo-block">
            @if(!empty($logoPath))
                <img src="{{ asset('storage/'.$logoPath) }}" alt="" style="max-height:0.24in;max-width:0.7in;">
            @else
                <div class="fallback-logo" aria-label="Printa Signages and Stickers">
                    <div class="logo-shapes" aria-hidden="true">
                        <span class="logo-tile tile-cyan"></span>
                        <span class="logo-tile tile-magenta"></span>
                        <span class="logo-tile tile-yellow"></span>
                    </div>
                    <div class="logo-word">PRINTA</div>
                    <div class="logo-subtitle">SIGNAGES &amp; STICKERS</div>
                </div>
            @endif
        </div>
        <div class="company-title">
            <h1>{{ strtoupper($companyName) }}</h1>
            <p>{{ $companyAddress }}</p>
            <p>{{ $companyPhone }}</p>
        </div>
    </div>
<p style="margin: 0 0 8px; font-size: 12px; text-align: right;"><strong>J.O./Task ID:</strong> {{ $task->task_id }}</p>
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
                <span class="amount">₱{{ number_format($totalAmount, 2) }}</span>
            </div>
            <div>
                <span class="label">DEPOSIT :</span>
                <span class="amount">₱{{ number_format($paidAmount, 2) }}</span>
            </div>
            <div>
                <span class="label">BALANCE :</span>
                <span class="amount">₱{{ number_format($balance, 2) }}</span>
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

</body>
<script>
    function saveSingleCopy() {
        // Hide the second copy (customer copy)
        const sheets = document.querySelectorAll('.sheet');
        const copyLabel = sheets[0].querySelector('.copy-label');
        sheets[1].style.display = 'none';
        copyLabel.style.display = 'none';
        
        // Trigger print dialog for save as PDF
        setTimeout(() => {
            window.print();
            // Restore both copies and label after print
            sheets[1].style.display = 'block';
            copyLabel.style.display = 'block';
        }, 100);
    }
</script>
</html>
