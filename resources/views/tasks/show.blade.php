@extends('layouts.app')

@section('title', 'Task Details')
@section('page-title', 'Task Details')


@section('content')
@php
    $paidAmount = (float) $task->receipts->sum('cash_received');
    $taskAmount = (float) $task->amount;
    $remainingAmount = max($taskAmount - $paidAmount, 0);
    $paymentProgress = $taskAmount > 0 ? min(100, round(($paidAmount / $taskAmount) * 100)) : 0;

    $pickupTime = null;
    if ($task->due_time) {
        $timeStr = strlen((string) $task->due_time) >= 5 ? substr((string) $task->due_time, 0, 5) : (string) $task->due_time;
        $pickupTime = \Carbon\Carbon::createFromFormat('H:i', $timeStr)->format('g:i A');
    }
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

                
                <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ $task->task_id }}</p>
                     
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
                                @case('Received')
                                    <span class="inline-block px-3 py-1 bg-blue-600 text-white rounded-full text-sm">Received</span>
                                @break
                                @case('Cancelled')
                                    <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-sm">Cancelled</span>
                                @break
                            @endswitch
                        </p>
                    
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

                    @if(auth()->user()->isAdmin())
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Assigned To</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $task->assignedTo?->name ?? 'Unassigned' }}</p>
                    </div>
                    @endif
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
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">To Be Picked Up</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $task->due_date->format('M d, Y') }}@if($pickupTime)<span class="text-gray-500 dark:text-gray-400 font-normal"> · </span>{{ $pickupTime }}@endif
                        </p>
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
       

        <!-- Image -->
        @if($task->image_path)
            @php
                $imageFilename = basename($task->image_path);
                $imageUrl = asset('storage/' . $task->image_path);
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="image" class="w-5 h-5 text-yellow-500"></i>
                        Task Image
                    </h3>
                    <div class="flex items-center gap-2">
                        <a href="{{ $imageUrl }}" target="_blank" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors text-xs font-semibold flex items-center gap-1.5" title="View Fullscreen">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            View
                        </a>
                        <a href="{{ $imageUrl }}" download="{{ $imageFilename }}" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-black rounded-lg transition-colors text-xs font-semibold flex items-center gap-1.5" title="Download Image">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            Download
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <img src="{{ $imageUrl }}" alt="Task image" class="max-w-full h-auto rounded-lg mx-auto shadow-sm">
                </div>
            </div>
        @endif

        <!-- Attachments -->
        @if(!empty($task->attachments) && count($task->attachments) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="paperclip" class="w-5 h-5 text-yellow-500"></i>
                        Task Attachments ({{ count($task->attachments) }})
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($task->attachments as $attachment)
                            @php
                                $filename = basename($attachment);
                                $extension = strtolower(pathinfo($attachment, PATHINFO_EXTENSION));
                                $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']);
                                $fileUrl = asset('storage/' . $attachment);
                            @endphp
                            <div class="flex flex-col border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900/50 hover:border-yellow-500 dark:hover:border-yellow-500 transition-all duration-200 shadow-sm group">
                                @if($isImage)
                                    <div class="h-32 bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden border-b border-gray-200 dark:border-gray-700 relative">
                                        <img src="{{ $fileUrl }}" alt="{{ $filename }}" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <a href="{{ $fileUrl }}" target="_blank" class="p-2 bg-white text-gray-900 rounded-full hover:bg-yellow-500 hover:text-black transition-colors" title="View Fullscreen">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ $fileUrl }}" download class="p-2 bg-white text-gray-900 rounded-full hover:bg-yellow-500 hover:text-black transition-colors" title="Download">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="h-32 bg-gray-100 dark:bg-gray-800 flex flex-col items-center justify-center border-b border-gray-200 dark:border-gray-700 relative">
                                        <i data-lucide="file-text" class="w-10 h-10 text-gray-400 group-hover:scale-110 transition-transform duration-300"></i>
                                        <span class="text-xs font-semibold uppercase px-2 py-0.5 bg-yellow-500/10 text-yellow-600 rounded mt-2">{{ $extension ?: 'file' }}</span>
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                            <a href="{{ $fileUrl }}" target="_blank" class="p-2 bg-white text-gray-900 rounded-full hover:bg-yellow-500 hover:text-black transition-colors" title="Open File">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </a>
                                            <a href="{{ $fileUrl }}" download class="p-2 bg-white text-gray-900 rounded-full hover:bg-yellow-500 hover:text-black transition-colors" title="Download">
                                                <i data-lucide="download" class="w-4 h-4"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <div class="p-3 flex items-center justify-between gap-2 bg-white dark:bg-gray-800">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate" title="{{ $filename }}">{{ $filename }}</p>
                                    </div>
                                    <a href="{{ $fileUrl }}" download class="p-1.5 text-gray-500 hover:text-yellow-600 dark:text-gray-400 dark:hover:text-yellow-500 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors" title="Download">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
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
                <a href="{{ route('tasks.job-order-print', $task) }}" class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                    Print Job Order
                </a>

                @if($task->status !== 'Received')
                @if(! in_array($task->status, ['Completed', 'Received', 'Cancelled'], true))
                <form action="{{ route('tasks.status', $task) }}" method="POST">
                    @csrf
                    <input type="hidden" name="status" value="Completed">
                    <button type="submit" class="block w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Mark as Complete
                    </button>
                </form>
                @endif

                @if($task->payment_status !== 'Paid')
                <a href="{{ route('receipts.create', ['task_id' => $task->id]) }}" class="block w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    Make a payment
                </a>
                @elseif($task->status === 'Completed')
                <form id="receiveTaskForm" action="{{ route('tasks.receive', $task) }}" method="POST">
                    @csrf
                    <button type="button" onclick="openReceiveModal()" class="block w-full px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors text-center flex items-center justify-center gap-2">
                        <i data-lucide="package-check" class="w-5 h-5"></i>
                        Received by Customer
                    </button>
                </form>
                @elseif($task->status === 'Received')
                <button type="button" disabled class="block w-full px-4 py-2 bg-green-500 text-white font-semibold rounded-lg text-center flex items-center justify-center gap-2 cursor-default">
                    <i data-lucide="package-check" class="w-5 h-5"></i>
                    Received by Customer
                </button>
                @else
                <button type="button" disabled class="block w-full px-4 py-2 bg-gray-500/40 text-white/80 font-semibold rounded-lg text-center flex items-center justify-center gap-2 cursor-not-allowed">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                    Waiting for Completion
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
                @endif
            </div>
        </div>

        <!-- Information -->
         <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            
         <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="col-span-2">
                      <div class="flex items-center gap-2">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Payment Status:
    </p>

    @switch($task->payment_status)
        @case('Unpaid')
            <span class="inline-block px-3 py-1 bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-sm">
                Unpaid
            </span>
        @break

        @case('Partial')
            <span class="inline-block px-3 py-1 bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 rounded-full text-sm">
                Partial
            </span>
        @break

        @case('Paid')
            <span class="inline-block px-3 py-1 bg-green-500/10 text-green-600 dark:text-green-400 rounded-full text-sm">
                Paid
            </span>
        @break
    @endswitch
</div>
                        <div class="flex items-center justify-between mb-2">
                           
                            
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
    </div>
</div>

@if($task->payment_status === 'Paid' && $task->status === 'Completed')
<div id="receiveModal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeReceiveModal()"></div>
    <div class="relative w-full max-w-md bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl overflow-hidden">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-green-500/10 text-green-600 dark:text-green-400">
                    <i data-lucide="package-check" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Confirm Customer Receipt</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Mark this task as received by the customer?
                    </p>
                    <div class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Task</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $task->task_id }} · {{ $task->customer_name }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <button type="button" onclick="closeReceiveModal()" class="px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors">
                Cancel
            </button>
            <button type="button" onclick="submitReceiveTask()" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors inline-flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                Confirm
            </button>
        </div>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    lucide.createIcons();

    function openReceiveModal() {
        const modal = document.getElementById('receiveModal');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closeReceiveModal() {
        const modal = document.getElementById('receiveModal');
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function submitReceiveTask() {
        const form = document.getElementById('receiveTaskForm');
        if (form) form.submit();
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeReceiveModal();
        }
    });
</script>
@endsection
