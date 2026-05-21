<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total expenses</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">&#8369;{{ number_format($totalExpenses, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Entries</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $expenses->count() }}</p>
    </div>
</div>

@if($categoryBreakdown->isNotEmpty())
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">By category</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($categoryBreakdown as $category => $amount)
                <span class="px-3 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-full text-sm">{{ $category }}: &#8369;{{ number_format($amount, 2) }}</span>
            @endforeach
        </div>
    </div>
@endif

<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Name</th>
                <th class="px-4 py-3 text-left font-semibold">Category</th>
                <th class="px-4 py-3 text-left font-semibold">Amount</th>
                <th class="px-4 py-3 text-left font-semibold">Date</th>
                <th class="px-4 py-3 text-left font-semibold">Recorded by</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($expenses as $expense)
                <tr>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $expense->expense_name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $expense->category }}</td>
                    <td class="px-4 py-3 font-semibold text-red-600 dark:text-red-400">&#8369;{{ number_format($expense->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $expense->date->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $expense->recordedBy?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No expenses in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
