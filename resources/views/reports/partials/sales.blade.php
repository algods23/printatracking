@php
    $startDate = $startDate ?? request('start_date');
    $endDate = $endDate ?? request('end_date');
@endphp
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total received</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($totalSales, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Discounts</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">&#8369;{{ number_format($totalDiscount, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Receipts</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $receipts->count() }}</p>
    </div>
</div>

@if($paymentMethodBreakdown->isNotEmpty())
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">By payment method</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($paymentMethodBreakdown as $method => $amount)
                <span class="px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-sm">{{ $method }}: &#8369;{{ number_format($amount, 2) }}</span>
            @endforeach
        </div>
    </div>
@endif

<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Receipt #</th>
                <th class="px-4 py-3 text-left font-semibold">Task</th>
                <th class="px-4 py-3 text-left font-semibold">Customer</th>
                <th class="px-4 py-3 text-left font-semibold">Method</th>
                <th class="px-4 py-3 text-left font-semibold">Amount</th>
                <th class="px-4 py-3 text-left font-semibold">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($receipts as $receipt)
                <tr>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $receipt->receipt_number }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $receipt->task?->task_id ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $receipt->customer_name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $receipt->payment_method }}</td>
                    <td class="px-4 py-3 font-semibold text-green-600 dark:text-green-400">&#8369;{{ number_format($receipt->cash_received, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $receipt->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No sales in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
