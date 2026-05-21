<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total tasks</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalTasks }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $completedTasks }}</p>
    </div>
    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Completion rate</p>
        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $completionRate }}%</p>
    </div>
</div>

@if($statusBreakdown->isNotEmpty())
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach($statusBreakdown as $status => $count)
            <span class="px-3 py-1 bg-gray-500/10 text-gray-700 dark:text-gray-300 rounded-full text-sm">{{ $status }}: {{ $count }}</span>
        @endforeach
    </div>
@endif

<div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Task ID</th>
                <th class="px-4 py-3 text-left font-semibold">Customer</th>
                <th class="px-4 py-3 text-left font-semibold">Assigned to</th>
                <th class="px-4 py-3 text-left font-semibold">Status</th>
                <th class="px-4 py-3 text-left font-semibold">Priority</th>
                <th class="px-4 py-3 text-left font-semibold">Amount</th>
                <th class="px-4 py-3 text-left font-semibold">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($tasks as $task)
                <tr>
                    <td class="px-4 py-3 text-yellow-600 dark:text-yellow-400 font-medium">{{ $task->task_id }}</td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $task->customer_name }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $task->assignedTo?->name ?? 'Unassigned' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $task->status }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $task->priority }}</td>
                    <td class="px-4 py-3 text-gray-900 dark:text-white">&#8369;{{ number_format($task->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $task->created_at->format('M d, Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No tasks in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
