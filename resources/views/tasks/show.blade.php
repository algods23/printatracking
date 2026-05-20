@extends('layouts.app')

@section('title', 'Task Details')
@section('page-title', $task->customer_name)
@section('page-subtitle', 'Task ID: ' . $task->task_id)

@section('content')
@php
    $paidAmount = (float) $task->receipts->sum('cash_received');
    $taskAmount = (float) $task->amount;
    $remainingAmount = max($taskAmount - $paidAmount, 0);
    $paymentProgress = $taskAmount > 0 ? min(100, round(($paidAmount / $taskAmount) * 100)) : 0;
@endphp

<a href="{{ route('tasks.index') }}" class="inline-flex items-center gap-2 mb-4 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
    <i data-lucide="arrow-left" class="w-4 h-4"></i>
    Back to All Tasks
</a>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Task Details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Details</h2>
                    <a href="{{ route('tasks.edit', $task) }}" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors flex items-center gap-2 text-sm">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                        Edit
                    </a>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Customer Name</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $task->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Contact Number</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $task->contact_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Product Type</p>
                        <p class="text-lg font-semibold"><span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-sm">{{ $task->product_type }}</span></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Assigned To</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $task->assignedTo?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Status</p>
                        <p class="text-lg font-semibold">
                            @switch($task->status)
                                @case('Pending')
                                    <span class="inline-block px-3 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded-full text-sm">Pending</span>
                                @break
                                @case('Designing')
                                    <span class="inline-block px-3 py-1 bg-purple-500/10 text-purple-600 dark:text-purple-400 rounded-full text-sm">Designing</span>
                                @break
                                @case('Printing')
                                    <span class="inline-block px-3 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-sm">Printing</span>
                                @break
                                @case('Installing')
                                    <span class="inline-block px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-full text-sm">Installing</span>
                                @break
                                @case('Completed')
                                    <span class="inline-block px-3 py-1 bg-green-500/10 text-green-600 dark:text-green-400 rounded-full text-sm">Completed</span>
                                @break
                                @case('Cancelled')
                                    <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-sm">Cancelled</span>
                                @break
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Priority</p>
                        <p class="text-lg font-semibold">
                            @switch($task->priority)
                                @case('Low')
                                    <span class="inline-block px-2 py-1 bg-gray-500/10 text-gray-600 dark:text-gray-400 rounded text-sm">Low</span>
                                @break
                                @case('Medium')
                                    <span class="inline-block px-2 py-1 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded text-sm">Medium</span>
                                @break
                                @case('High')
                                    <span class="inline-block px-2 py-1 bg-orange-500/10 text-orange-600 dark:text-orange-400 rounded text-sm">High</span>
                                @break
                                @case('Urgent')
                                    <span class="inline-block px-2 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded text-sm">Urgent</span>
                                @break
                            @endswitch
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Amount</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">&#8369;{{ number_format($task->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Payment Status</p>
                        <p class="text-lg font-semibold">
                            @switch($task->payment_status)
                                @case('Unpaid')
                                    <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-sm">Unpaid</span>
                                @break
                                @case('Partial')
                                    <span class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-full text-sm">Partial</span>
                                @break
                                @case('Paid')
                                    <span class="inline-block px-3 py-1 bg-green-500/10 text-green-600 dark:text-green-400 rounded-full text-sm">Paid</span>
                                @break
                            @endswitch
                        </p>
                    </div>
                    <div class="col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Payment Progress</p>
                            
                        </div>
                        <div
                            class="relative h-6 rounded-full overflow-hidden border border-gray-500/30"
                            style="background: linear-gradient(to right, #22c55e {{ $paymentProgress }}%, #374151 {{ $paymentProgress }}%);"
                            aria-label="Payment progress {{ $paymentProgress }}%"
                        >
                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white drop-shadow">
                                {{ $paymentProgress }}%
                            </span>
                        </div>
                        <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Total</p>
                                <p class="font-semibold text-gray-900 dark:text-white">&#8369;{{ number_format($taskAmount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Paid</p>
                                <p class="font-semibold text-green-600 dark:text-green-400">&#8369;{{ number_format($paidAmount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Balance</p>
                                <p class="font-semibold text-yellow-600 dark:text-yellow-400">&#8369;{{ number_format($remainingAmount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Due Date</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $task->due_date->format('M d, Y') }}</p>
                    </div>
                </div>

                @if($task->items->isNotEmpty())
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Job Orders</h3>
                        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                            <table class="w-full min-w-[620px] text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold">Job Order</th>
                                        <th class="px-4 py-3 text-left font-semibold">Qty</th>
                                        <th class="px-4 py-3 text-left font-semibold">Price</th>
                                        <th class="px-4 py-3 text-left font-semibold">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($task->items as $item)
                                        <tr>
                                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $item->job_order }}</td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $item->quantity }}</td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">&#8369;{{ number_format($item->price, 2) }}</td>
                                            <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">&#8369;{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($task->notes)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Notes</p>
                        <p class="text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $task->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Payment History</h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($task->receipts->sortByDesc('created_at') as $receipt)
                    <div class="p-6 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $receipt->receipt_number }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $receipt->created_at->format('M d, Y - h:i A') }} via {{ $receipt->payment_method }}</p>
                            @if($receipt->payment_reference)
                                <p class="text-sm text-gray-500 dark:text-gray-400">Ref: {{ $receipt->payment_reference }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-green-600 dark:text-green-400">&#8369;{{ number_format($receipt->cash_received, 2) }}</p>
                            <a href="{{ route('receipts.show', $receipt) }}" class="text-sm text-yellow-600 dark:text-yellow-400 hover:underline">View receipt</a>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-gray-500 dark:text-gray-400">No payments recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Image -->
        @if($task->image_path)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Task Image</h3>
                </div>
                <div class="p-6">
                    <img src="{{ asset('storage/' . $task->image_path) }}" alt="Task image" class="max-w-full h-auto rounded-lg">
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="font-semibold text-gray-900 dark:text-white">Actions</h3>
            </div>
            <div class="p-6 space-y-3">
                @if($remainingAmount > 0)
                <a href="{{ route('receipts.create', ['task_id' => $task->id]) }}" class="block w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    Create Receipt
                </a>
                @else
                <button type="button" disabled class="block w-full px-4 py-2 bg-green-500/40 text-white/80 font-semibold rounded-lg text-center flex items-center justify-center gap-2 cursor-not-allowed">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    Fully Paid
                </button>
                @endif
                <a href="{{ route('tasks.edit', $task) }}" class="block w-full px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                    <i data-lucide="edit" class="w-5 h-5"></i>
                    Edit Task
                </a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="block w-full px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-5 h-5"></i>
                        Delete Task
                    </button>
                </form>
            </div>
        </div>

        <!-- Information -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 space-y-4">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">TASK ID</p>
                <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $task->task_id }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">CREATED</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $task->created_at->format('M d, Y - h:i A') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">LAST UPDATED</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ $task->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    lucide.createIcons();
</script>
@endsection
