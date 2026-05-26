<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Disbursement</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">&#8369;{{ number_format($totalExpenses, 2) }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Days with Disbursement</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($dailySummary) }}</p>
    </div>
</div>

<div class="space-y-6">
    <!-- Summary Table -->
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Daily Summary</h3>
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Date</th>
                        <th class="px-4 py-3 text-left font-semibold">Number of Entries</th>
                        <th class="px-4 py-3 text-left font-semibold">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($dailySummary as $day)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $day['date']->format('M d, Y') }} ({{ $day['date']->format('l') }})</td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $day['count'] }}</td>
                            <td class="px-4 py-3 font-semibold text-red-600 dark:text-red-400">&#8369;{{ number_format($day['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No daily summaries.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Grouped Breakdown -->
    @if(count($dailySummary) > 0)
    <div>
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Grouped Items Breakdown</h3>
        <div class="space-y-4">
            @foreach($dailySummary as $day)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden shadow-sm">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $day['date']->format('F d, Y') }}</span>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400">&#8369;{{ number_format($day['total'], 2) }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-100/50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Disbursement Name</th>
                                    <th class="px-4 py-2 text-left font-semibold">Category</th>
                                    <th class="px-4 py-2 text-left font-semibold">Recorded By</th>
                                    <th class="px-4 py-2 text-right font-semibold">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($day['items'] as $item)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-950 dark:text-white font-medium">{{ $item->expense_name }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $item->category }}</td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $item->recordedBy?->name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-red-600 dark:text-red-400">&#8369;{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
