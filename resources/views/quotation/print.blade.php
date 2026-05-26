<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing</title>
    <style>
        @page { margin: 18px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { margin-bottom: 14px; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { color: #6b7280; font-size: 11px; }
        .customer-card { margin: 14px 0 16px; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px 14px; }
        .customer-label { color: #6b7280; font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .customer-name { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .summary { width: 100%; border-collapse: collapse; margin: 12px 0 18px; table-layout: fixed; }
        .summary th, .summary td { border: 1px solid #d1d5db; padding: 10px 12px; text-align: center; }
        .summary th { background: #f3f4f6; font-weight: bold; }
        .summary .value { font-size: 14px; font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #d1d5db; padding: 8px 10px; text-align: left; }
        .table th { background: #e5e7eb; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; background: #fee2e2; color: #b91c1c; font-size: 11px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Billing Statement</div>
        <div class="subtitle">Generated {{ $generatedAt->format('M d, Y h:i A') }}{{ $search ? ' for "' . $search . '"' : '' }}</div>
    </div>

    @if($customerName)
        <div class="customer-card">
            <div class="customer-label">Customer Name</div>
            <div class="customer-name">{{ $customerName }}</div>
            @if($customerContact)
                <div class="muted">{{ $customerContact }}</div>
            @endif
        </div>
    @endif

    <table class="summary">
        <tr>
            <th>Total</th>
            <th>Deposit</th>
            <th>Balance</th>
        </tr>
        <tr>
            <td class="value">₱{{ number_format($totalAmount, 2) }}</td>
            <td class="value">₱{{ number_format($totalDeposit, 2) }}</td>
            <td class="value">₱{{ number_format($totalBalance, 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Billing ID</th>
                @if($showCustomerColumn)
                    <th>Customer</th>
                @endif
                <th class="right">Total</th>
                <th class="right">Deposit</th>
                <th class="right">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotations as $task)
                <tr>
                    <td>{{ $task->created_at->format('M d, Y') }}</td>
                    <td>{{ $task->task_id }}</td>
                    @if($showCustomerColumn)
                        <td>
                            {{ $task->customer_name }}<br>
                            <span class="muted">{{ $task->contact_number }}</span>
                        </td>
                    @endif
                    <td class="right">₱{{ number_format($task->amount, 2) }}</td>
                    <td class="right">₱{{ number_format($task->paid_amount ?? 0, 2) }}</td>
                    <td class="right">₱{{ number_format($task->balance, 2) }}</td>
                    <td><span class="badge">{{ $task->payment_status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showCustomerColumn ? 7 : 6 }}">No billing data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>