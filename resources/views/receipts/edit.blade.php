@extends('layouts.app')

@section('title', 'Edit Receipt')
@section('page-title', $receipt->receipt_number)
@section('page-subtitle', 'Update payment details for ' . $receipt->task->task_id)

@section('content')
@php
    $task = $receipt->task;
    $paidAmount = (float) $task->receipts->sum('cash_received');
    $taskAmount = (float) $task->amount;
    $remainingAmount = max($taskAmount - $paidAmount, 0);
    $paymentProgress = $taskAmount > 0 ? min(100, round(($paidAmount / $taskAmount) * 100)) : 0;
    $currentMethod = old('payment_method', $receipt->payment_channel ?? $receipt->payment_method);
@endphp

<div class="max-w-4xl space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Receipt</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $receipt->created_at->format('M d, Y - h:i A') }}</p>
            </div>
            <a href="{{ route('receipts.show', $receipt) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors text-sm">Back to Receipt</a>
        </div>

        <form action="{{ route('receipts.update', $receipt) }}" method="POST" class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Task</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $task->task_id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Customer</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $receipt->customer_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $receipt->customer_phone }}</p>
                    </div>
                    <div>
                        <label for="payment_amount" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Received</label>
                        <input id="payment_amount" name="payment_amount" type="number" min="0.01" step="0.01" value="{{ old('payment_amount', $receipt->cash_received) }}" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('payment_amount') border-red-500 @enderror">
                        @error('payment_amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Method</label>
                        <select id="payment_method" name="payment_method" onchange="toggleReferenceField()" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('payment_method') border-red-500 @enderror">
                            @foreach(['Cash', 'Card', 'Check', 'Bank Transfer', 'GCash', 'Maya', 'Other'] as $method)
                                <option value="{{ $method }}" @selected($currentMethod === $method)>{{ $method }}</option>
                            @endforeach
                        </select>
                        @error('payment_method')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="referenceField">
                        <label for="payment_reference" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Ref (if have a reference)</label>
                        <input id="payment_reference" name="payment_reference" type="text" value="{{ old('payment_reference', $receipt->payment_reference) }}" placeholder="Enter reference number" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('payment_reference') border-red-500 @enderror">
                        @error('payment_reference')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 @error('notes') border-red-500 @enderror">{{ old('notes', $receipt->notes) }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Current Payment Summary</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Current payment method</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $receipt->display_payment_method }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Current reference</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $receipt->payment_reference ?? 'None' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-gray-500 dark:text-gray-400">Current payment received</span>
                            <span class="font-semibold text-green-600 dark:text-green-400">&#8369;{{ number_format($receipt->cash_received, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Task Payment Progress</h3>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Current progress</span>
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

                <div class="flex gap-3">
                    <a href="{{ route('receipts.show', $receipt) }}" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors text-center">Cancel</a>
                    <button type="submit" class="flex-1 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleReferenceField() {
        const method = document.getElementById('payment_method').value;
        const referenceField = document.getElementById('referenceField');
        referenceField.classList.toggle('hidden', method === 'Cash');
    }

    toggleReferenceField();
    lucide.createIcons();
</script>
@endsection
