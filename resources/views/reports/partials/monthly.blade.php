<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total sales</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($totalSales, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total disbursement</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">&#8369;{{ number_format($totalExpenses, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total PCV</p>
        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">&#8369;{{ number_format($totalPcv, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
        <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">&#8369;{{ number_format($netProfit, 2) }}</p>
    </div>
</div>

<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Month</th>
                <th class="px-4 py-3 text-left font-semibold">Sales</th>
                <th class="px-4 py-3 text-left font-semibold">Disbursement</th>
                <th class="px-4 py-3 text-left font-semibold">PCV</th>
                <th class="px-4 py-3 text-left font-semibold">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($monthlyRows as $row)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['label'] }}</td>
                    <td class="px-4 py-3 text-green-600 dark:text-green-400">&#8369;{{ number_format($row['sales'], 2) }}</td>
                    <td class="px-4 py-3 text-red-600 dark:text-red-400">&#8369;{{ number_format($row['expenses'], 2) }}</td>
                    <td class="px-4 py-3 text-orange-600 dark:text-orange-400">&#8369;{{ number_format($row['pcv'], 2) }}</td>
                    <td class="px-4 py-3 font-semibold {{ $row['total'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">&#8369;{{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No data for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
