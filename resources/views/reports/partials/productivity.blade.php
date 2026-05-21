<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Staff</th>
                <th class="px-4 py-3 text-left font-semibold">Total tasks</th>
                <th class="px-4 py-3 text-left font-semibold">Completed</th>
                <th class="px-4 py-3 text-left font-semibold">Pending</th>
                <th class="px-4 py-3 text-left font-semibold">Completion %</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($staffProductivity as $row)
                @php
                    $rate = $row['total'] > 0 ? round(($row['completed'] / $row['total']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['total'] }}</td>
                    <td class="px-4 py-3 text-green-600 dark:text-green-400">{{ $row['completed'] }}</td>
                    <td class="px-4 py-3 text-orange-600 dark:text-orange-400">{{ $row['pending'] }}</td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $rate }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No task activity in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
