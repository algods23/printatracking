<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Billing</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">&#8369;{{ number_format($totalAmount, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Deposit</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($totalDeposit, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Balance</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">&#8369;{{ number_format($totalBalance, 2) }}</p>
    </div>
</div>

<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Date</th>
                <th class="px-4 py-3 text-left font-semibold">Billing ID</th>
                <th class="px-4 py-3 text-left font-semibold">Customer</th>
                <th class="px-4 py-3 text-left font-semibold">Total</th>
                <th class="px-4 py-3 text-left font-semibold">Deposit</th>
                <th class="px-4 py-3 text-left font-semibold">Balance</th>
                <th class="px-4 py-3 text-left font-semibold">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($tasks as $task)
                <tr>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $task->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ $task->task_id }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                        <div>{{ $task->customer_name }}</div>
                        <div class="text-xs text-gray-500">{{ $task->contact_number }}</div>
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">&#8369;{{ number_format($task->amount, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-green-600 dark:text-green-400">&#8369;{{ number_format($task->receipts->sum('cash_received'), 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-red-600 dark:text-red-400">&#8369;{{ number_format($task->balance, 2) }}</td>
                    <td class="px-4 py-3">
                        @switch($task->payment_status)
                            @case('Unpaid')
                                <span class="inline-block px-2 py-0.5 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-xs font-medium">Unpaid</span>
                            @break
                            @case('Partial')
                                <span class="inline-block px-2 py-0.5 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-full text-xs font-medium">Partial</span>
                            @break
                            @default
                                <span class="inline-block px-2 py-0.5 bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-full text-xs font-medium">{{ $task->payment_status }}</span>
                        @endswitch
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No outstanding billing in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
