@extends('layouts.app')

@section('title', 'Tasks')
@section('page-title', 'Transactions')
@section('page-subtitle', 'Manage all your printing and signage tasks')

@section('content')
<!-- Search & Filter Bar -->
<form id="taskFiltersForm" action="{{ route('tasks.index') }}" method="GET" class="mb-6 flex items-end gap-3 flex-wrap">
    <div class="flex-1 min-w-[200px]">
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $query ?? request('q') }}" placeholder="Search by task ID, customer name, or phone..." class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
            <button type="submit" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors text-sm">Search</button>
        </div>
    </div>
    <select onchange="filterTasks()" id="statusFilter" name="status" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
        <option value="">All Status</option>
        <option value="Pending">Pending</option>
        <option value="Completed">Completed</option>
        <option value="Received">Received</option>
        <option value="Cancelled">Cancelled</option>
    </select>
    <select onchange="filterTasks()" id="priorityFilter" name="priority" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
        <option value="">All Priority</option>
        <option value="Low">Low</option>
        <option value="Urgent">Urgent</option>
    </select>
    <input type="hidden" id="archivedFilter" name="archived" value="{{ request()->boolean('archived') ? '1' : '' }}">
    <input type="hidden" id="paymentStatusFilter" name="payment_status" value="{{ request('payment_status') }}">
    <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Due From</label>
        <input type="date" id="dueDateFrom" name="due_date_from" onchange="filterTasks()" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Due To</label>
        <input type="date" id="dueDateTo" name="due_date_to" onchange="filterTasks()" class="px-3 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm">
    </div>
    <button type="button" onclick="clearFilters()" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors text-sm">
        Clear
    </button>
    <button type="button" onclick="showArchived()" class="px-3 py-2 bg-emerald-500 hover:bg-emerald-600 text-white font-semibold rounded-lg transition-colors text-sm">
        Archived
    </button>
    <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2 text-sm">
        <i data-lucide="plus" class="w-4 h-4"></i>
        New Task
    </a>
</form>

<!-- Tasks Table -->
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Created Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Job Order #</th>
                    
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Customer</th>
                    @if(auth()->user()->isAdmin())
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Staff Assigned</th>
                    @endif
                    
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Priority</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Due Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Amount</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Payment Status</th>
                    
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->created_at->format('M d, Y') }}
                        </td>
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
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                            <a href="{{ route('tasks.show', $task) }}" class="text-yellow-500 hover:text-yellow-600">{{ $task->task_id }}</a>
                        </td>
                        
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            <div>{{ $task->customer_name }}</div>
                            <div class="text-xs text-gray-500">{{ $task->contact_number }}</div>
                        </td>
                        
                        @if(auth()->user()->isAdmin())
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->assignedTo?->name ?? 'Unassigned' }}
                        </td>
                        @endif
                       
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
                          <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $task->due_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                            ₱{{ number_format($task->amount, 2) }}
                        </td>
                      
                        <td class="px-6 py-4 text-sm">
                            @switch($task->payment_status)
                                @case('Unpaid')
                                    <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-xs font-medium">Unpaid</span>
                                @break
                                @case('Partial')
                                    <span class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-full text-xs font-medium">Partial</span>
                                @break
                                @case('Paid')
                                    <span class="inline-block px-3 py-1 bg-green-500/10 text-green-600 dark:text-green-400 rounded-full text-xs font-medium">Paid</span>
                                @break
                                @default
                                    <span class="inline-block px-3 py-1 bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded-full text-xs font-medium">{{ $task->payment_status ?? 'N/A' }}</span>
                            @endswitch
                        </td>

                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                @if($task->status === 'Cancelled')
                                    <a href="{{ route('tasks.show', $task) }}" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-500/10 rounded transition-colors" title="View cancellation reason">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                @else
                                    <a href="{{ route('tasks.show', $task) }}" class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-500/10 rounded transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    @if($task->status !== 'Received')
                                    <a href="{{ route('tasks.edit', $task) }}" class="p-2 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-500/10 rounded transition-colors">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                    @if(auth()->user()->isAdmin())
                                    <button type="button" onclick="openCancelModal({{ $task->id }})" class="p-2 text-red-600 dark:text-red-400 hover:bg-red-500/10 rounded transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 11 : 10 }}" class="px-6 py-12 text-center">
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

<!-- Cancel Task Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 max-w-md w-full mx-4 shadow-lg">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cancel Task</h3>
        <form id="cancelForm" method="POST" class="space-y-4">
            @csrf
            @method('DELETE')
            <div>
                <label for="cancellation_reason" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Cancellation Reason (Optional)</label>
                <textarea id="cancellation_reason" name="cancellation_reason" rows="4" placeholder="Enter the reason for cancelling this task..." class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeCancelModal()" class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-colors">
                    Confirm Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function filterTasks() {
        document.getElementById('taskFiltersForm').submit();
    }

    function clearFilters() {
        window.location.href = '{{ route('tasks.index') }}';
    }

    function showArchived() {
        document.getElementById('archivedFilter').value = '1';
        document.getElementById('statusFilter').value = 'Received';
        document.getElementById('paymentStatusFilter').value = 'Paid';
        document.getElementById('taskFiltersForm').submit();
    }

    function openCancelModal(taskId) {
        const modal = document.getElementById('cancelModal');
        const form = document.getElementById('cancelForm');
        form.action = `/tasks/${taskId}`;
        modal.classList.remove('hidden');
    }

    function closeCancelModal() {
        const modal = document.getElementById('cancelModal');
        modal.classList.add('hidden');
        document.getElementById('cancellation_reason').value = '';
    }

    // Restore filter values from URL params
    (function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('status')) document.getElementById('statusFilter').value = params.get('status');
        if (params.get('priority')) document.getElementById('priorityFilter').value = params.get('priority');
        if (params.get('archived') === '1') document.getElementById('archivedFilter').value = '1';
        if (params.get('payment_status')) document.getElementById('paymentStatusFilter').value = params.get('payment_status');
        if (params.get('due_date_from')) document.getElementById('dueDateFrom').value = params.get('due_date_from');
        if (params.get('due_date_to')) document.getElementById('dueDateTo').value = params.get('due_date_to');
    })();

    lucide.createIcons();
</script>
@endsection
