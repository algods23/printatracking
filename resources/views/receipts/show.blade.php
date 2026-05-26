@extends('layouts.app')

@section('title', 'Receipt Details')
@section('page-title', $receipt->receipt_number)
@section('page-subtitle', 'Payment for ' . $receipt->task->task_id)

@section('content')
@php
    $task = $receipt->task;
    $paidAmount = (float) $task->receipts->sum('cash_received');
    $taskAmount = (float) $task->amount;
    $remainingAmount = max($taskAmount - $paidAmount, 0);
    $paymentProgress = $taskAmount > 0 ? min(100, round(($paidAmount / $taskAmount) * 100)) : 0;
@endphp

<div class="max-w-4xl space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Receipt</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $receipt->created_at->format('M d, Y - h:i A') }}</p>
            </div>
            <a href="{{ route('tasks.show', $task) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors text-sm">Back to Task</a>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Customer</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $receipt->customer_name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $receipt->customer_phone }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Payment Method</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $receipt->display_payment_method }}</p>
                @if($receipt->payment_reference)
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ref: {{ $receipt->payment_reference }}</p>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Payment Received</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($receipt->cash_received, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Issued By</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $receipt->issuedBy?->name ?? 'Unknown' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="font-semibold text-gray-900 dark:text-white">Task Payment Progress</h3>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $paymentProgress }}%</span>
        </div>
        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-green-500 rounded-full" style="width: {{ $paymentProgress }}%"></div>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-4 text-sm">
            <div>
                <p class="text-gray-500 dark:text-gray-400">Total</p>
                <p class="font-semibold text-gray-900 dark:text-white">&#8369;{{ number_format($taskAmount, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Paid</p>
                <p class="font-semibold text-green-600 dark:text-green-400">&#8369;{{ number_format($paidAmount, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500 dark:text-gray-400">Balance</p>
                <p class="font-semibold text-yellow-600 dark:text-yellow-400">&#8369;{{ number_format($remainingAmount, 2) }}</p>
            </div>
        </div>
    </div>

    @if($receipt->notes)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Notes</h3>
            <p class="text-gray-700 dark:text-gray-200 whitespace-pre-wrap">{{ $receipt->notes }}</p>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
