<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .header { margin-bottom: 18px; }
        .title { font-size: 22px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { color: #6b7280; font-size: 11px; }
        .summary { width: 100%; border-collapse: collapse; margin: 18px 0; }
        .summary td { border: 1px solid #d1d5db; padding: 8px 10px; }
        .summary .label { background: #f3f4f6; font-weight: bold; }
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
        <div class="title">Quotation</div>
        <div class="subtitle">Generated {{ $generatedAt->format('M d, Y h:i A') }}{{ $search ? ' for "' . $search . '"' : '' }}</div>
    </div>

    <table class="summary">
        <tr>
            <td class="label">Total</td>
            <td>₱{{ number_format($totalAmount, 2) }}</td>
            <td class="label">Deposit</td>
            <td>₱{{ number_format($totalDeposit, 2) }}</td>
            <td class="label">Balance</td>
            <td>₱{{ number_format($totalBalance, 2) }}</td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Quotation ID</th>
                <th>Customer</th>
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
                    <td>
                        {{ $task->customer_name }}<br>
                        <span class="muted">{{ $task->contact_number }}</span>
                    </td>
                    <td class="right">₱{{ number_format($task->amount, 2) }}</td>
                    <td class="right">₱{{ number_format($task->paid_amount ?? 0, 2) }}</td>
                    <td class="right">₱{{ number_format($task->balance, 2) }}</td>
                    <td><span class="badge">{{ $task->payment_status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No quotation data found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>