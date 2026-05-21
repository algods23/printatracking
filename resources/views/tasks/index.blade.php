@extends('layouts.app')

@section('title', 'Tasks')
@section('page-title', 'Tasks Management')
@section('page-subtitle', 'Manage all your printing and signage tasks')

@section('content')
<!-- Search & Filter Bar -->
<div class="mb-6 flex items-end gap-3 flex-wrap">
    <div class="flex-1 min-w-[200px]">
        <form action="{{ route('tasks.search') }}" method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Search by task ID, customer name, or phone..." class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
            <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors text-sm">Search</button>
        </form>
    </div>
    <select onchange="filterTasks()" id="statusFilter" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
        <option value="">All Status</option>
        <option value="Pending">Pending</option>
        <option value="Designing">Designing</option>
        <option value="Printing">Printing</option>
        <option value="Installing">Installing</option>
        <option value="Completed">Completed</option>
        <option value="Received">Received</option>
        <option value="Cancelled">Cancelled</option>
    </select>
    <select onchange="filterTasks()" id="priorityFilter" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
        <option value="">All Priority</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
        <option value="Urgent">Urgent</option>
    </select>
    <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Due From</label>
        <input type="date" id="dueDateFrom" onchange="filterTasks()" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Due To</label>
        <input type="date" id="dueDateTo" onchange="filterTasks()" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
    </div>
    <button onclick="clearFilters()" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors text-sm">
        Clear
    </button>
    <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2 text-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        New Task
    </a>
</div>

<!-- Tasks Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Task ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Customer</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Product</th>
                    @if(auth()->user()->isAdmin())
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Assigned To</th>
                    @endif
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Priority</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Amount</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Due Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                            <a href="{{ route('tasks.show', $task) }}" class="text-yellow-500 hover:text-yellow-600">{{ $task->task_id }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            <div>{{ $task->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $task->contact_number }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            <span class="inline-block px-2 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded text-xs font-medium">{{ $task->product_type }}</span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->assignedTo?->name ?? 'Unassigned' }}
                        </td>
                        @endif
                        <td class="px-6 py-4 text-sm">
                            @switch($task->status)
                                @case('Pending')
                                    <span class="inline-block px-3 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-full text-xs font-medium">Pending</span>
                                @break
                                @case('Designing')
                                    <span class="inline-block px-3 py-1 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-full text-xs font-medium">Designing</span>
                                @break
                                @case('Printing')
                                    <span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">Printing</span>
                                @break
                                @case('Installing')
                                    <span class="inline-block px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-full text-xs font-medium">Installing</span>
                                @break
                                @case('Completed')
                                    <span class="inline-block px-3 py-1 bg-green-500/10 text-green-600 dark:text-green-400 rounded-full text-xs font-medium">Completed</span>
                                @break
                                @case('Received')
                                    <span class="inline-block px-3 py-1 bg-blue-600 text-white rounded-full text-xs font-medium">Received</span>
                                @break
                                @case('Cancelled')
                                    <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-xs font-medium">Cancelled</span>
                                @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @switch($task->priority)
                                @case('Low')
                                    <span class="inline-block px-2 py-1 bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded text-xs font-medium">Low</span>
                                @break
                                @case('Medium')
                                    <span class="inline-block px-2 py-1 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded text-xs font-medium">Medium</span>
                                @break
                                @case('High')
                                    <span class="inline-block px-2 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded text-xs font-medium">High</span>
                                @break
                                @case('Urgent')
                                    <span class="inline-block px-2 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded text-xs font-medium">Urgent</span>
                                @break
                            @endswitch
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                            ₱{{ number_format($task->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->due_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('tasks.show', $task) }}" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-500/10 rounded transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if($task->status !== 'Received')
                                <a href="{{ route('tasks.edit', $task) }}" class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/10 rounded transition-colors">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </a>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <i data-lucide="inbox" class="w-12 h-12 text-gray-400"></i>
                                <p class="text-gray-500 dark:text-gray-400">No tasks found</p>
                                <a href="{{ route('tasks.create') }}" class="text-yellow-500 hover:text-yellow-600 text-sm font-semibold">Create a new task</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $tasks->links() }}
</div>

@endsection

@section('scripts')
<script>
    function filterTasks() {
        const status = document.getElementById('statusFilter').value;
        const priority = document.getElementById('priorityFilter').value;
        const dueDateFrom = document.getElementById('dueDateFrom').value;
        const dueDateTo = document.getElementById('dueDateTo').value;
        
        let url = '{{ route('tasks.filter') }}?';
        if (status) url += 'status=' + status + '&';
        if (priority) url += 'priority=' + priority + '&';
        if (dueDateFrom) url += 'due_date_from=' + dueDateFrom + '&';
        if (dueDateTo) url += 'due_date_to=' + dueDateTo + '&';
        
        window.location.href = url;
    }

    function clearFilters() {
        window.location.href = '{{ route('tasks.index') }}';
    }

    // Restore filter values from URL params
    (function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('status')) document.getElementById('statusFilter').value = params.get('status');
        if (params.get('priority')) document.getElementById('priorityFilter').value = params.get('priority');
        if (params.get('due_date_from')) document.getElementById('dueDateFrom').value = params.get('due_date_from');
        if (params.get('due_date_to')) document.getElementById('dueDateTo').value = params.get('due_date_to');
    })();

    lucide.createIcons();
</script>
@endsection
