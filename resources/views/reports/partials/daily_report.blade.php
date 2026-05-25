<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Sales</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($totalSales, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Expenses</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">&#8369;{{ number_format($totalExpenses, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Net Profit</p>
        <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">&#8369;{{ number_format($netProfit, 2) }}</p>
    </div>
</div>

<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Date</th>
                <th class="px-4 py-3 text-right font-semibold">Sales</th>
                <th class="px-4 py-3 text-right font-semibold">Expenses</th>
                <th class="px-4 py-3 text-right font-semibold">Profit</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($dailyRows as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">{{ $row['date']->format('M d, Y') }} ({{ $row['date']->format('l') }})</td>
                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400 font-semibold">&#8369;{{ number_format($row['sales'], 2) }}</td>
                    <td class="px-4 py-3 text-right text-red-600 dark:text-red-400">&#8369;{{ number_format($row['expenses'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $row['profit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        &#8369;{{ number_format($row['profit'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No activity in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
