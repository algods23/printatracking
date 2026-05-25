@extends('layouts.app')

@section('title', 'Create Receipt')
@section('page-title', 'Create Receipt')
@section('page-subtitle', 'Record a downpayment or full payment')

@section('content')
@php
    $selectedTaskId = (string) old('task_id', $selectedTask?->id);
    $taskOptions = $tasks->map(function ($task) {
        $paidAmount = (float) ($task->paid_amount ?? 0);
        $amount = (float) $task->amount;

        return [
            'id' => $task->id,
            'task_id' => $task->task_id,
            'customer_name' => $task->customer_name,
            'contact_number' => $task->contact_number,
            'product_type' => $task->product_type,
            'amount' => $amount,
            'paid' => $paidAmount,
            'remaining' => max($amount - $paidAmount, 0),
            'progress' => $amount > 0 ? min(100, round(($paidAmount / $amount) * 100)) : 0,
        ];
    })->values();
@endphp

<div
    class="max-w-5xl"
    x-data="receiptForm({
        tasks: @js($taskOptions),
        selectedTaskId: '{{ $selectedTaskId }}',
        oldAmount: '{{ old('payment_amount') }}',
        paymentMethod: '{{ old('payment_method', 'Cash') }}'
    })"
    x-init="init()"
>
    <form action="{{ route('receipts.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf

        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Details</h2>
            </div>

            <div class="p-6 space-y-6">
                <input type="hidden" name="task_id" value="{{ $selectedTask->id }}">

                <div>
                    <p class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Task</p>
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $selectedTask->task_id }} - {{ $selectedTask->customer_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedTask->product_type }} | {{ $selectedTask->contact_number }}</p>
                    </div>
                </div>

                <template x-if="selectedTask">
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="selectedTask.customer_name"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="`${selectedTask.product_type} | ${selectedTask.contact_number}`"></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Balance</p>
                                <p class="text-xl font-bold text-yellow-600 dark:text-yellow-400" x-text="money(selectedTask.remaining)"></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="flex items-center justify-between mb-2 text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Paid progress</span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="`${selectedTask.progress}%`"></span>
                            </div>
                            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full" :style="`width: ${selectedTask.progress}%`"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Total</p>
                                    <p class="font-semibold text-gray-900 dark:text-white" x-text="money(selectedTask.amount)"></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Paid</p>
                                    <p class="font-semibold text-green-600 dark:text-green-400" x-text="money(selectedTask.paid)"></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-gray-400">Remaining</p>
                                    <p class="font-semibold text-yellow-600 dark:text-yellow-400" x-text="money(selectedTask.remaining)"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="payment_amount" class="block text-sm font-medium text-gray-900 dark:text-white">Payment Amount</label>
                        <div class="flex gap-2" x-show="selectedTask">
                            <button type="button" @click="useDownpayment()" class="px-3 py-1 text-xs rounded bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600">Downpayment 50%</button>
                            <button type="button" @click="useRemaining()" class="px-3 py-1 text-xs rounded bg-yellow-500 text-black hover:bg-yellow-600">Pay Balance</button>
                        </div>
                    </div>
                    <input id="payment_amount" name="payment_amount" type="number" min="0.01" step="0.01" x-model="paymentAmount" :max="selectedTask ? selectedTask.remaining : null" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="selectedTask">Enter any downpayment amount up to the remaining balance.</p>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Method</label>
                    <select id="payment_method" name="payment_method" x-model="paymentMethod" required class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        @foreach(['Cash', 'Card', 'Check', 'Bank Transfer', 'GCash', 'Maya', 'Other'] as $method)
                            <option value="{{ $method }}" @selected(old('payment_method', 'Cash') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="needsReference()" x-cloak>
                    <label for="payment_reference" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Reference No.</label>
                    <input id="payment_reference" name="payment_reference" type="text" value="{{ old('payment_reference') }}" :required="needsReference()" placeholder="Enter reference number" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Required for non-cash payments.</p>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">After This Payment</h3>
                <div class="space-y-4" x-show="selectedTask">
                    <div>
                        <div class="flex items-center justify-between mb-2 text-sm">
                            <span class="text-gray-500 dark:text-gray-400">New progress</span>
                            <span class="font-semibold text-gray-900 dark:text-white" x-text="`${newProgress()}%`"></span>
                        </div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" :style="`width: ${newProgress()}%`"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">New paid total</span>
                        <span class="font-semibold text-green-600 dark:text-green-400" x-text="money(newPaid())"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">New balance</span>
                        <span class="font-semibold text-yellow-600 dark:text-yellow-400" x-text="money(newBalance())"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold" :class="newBalance() <= 0 ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400'" x-text="newBalance() <= 0 ? 'Paid' : 'Partial'"></span>
                    </div>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400" x-show="!selectedTask">Select a task to preview payment progress.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ $selectedTask ? route('tasks.show', $selectedTask) : route('tasks.index') }}" class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white font-semibold rounded-lg transition-colors text-center">Cancel</a>
                <button type="submit" class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg transition-colors">Save Receipt</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function receiptForm(config) {
        return {
            tasks: config.tasks,
            selectedTaskId: config.selectedTaskId,
            paymentAmount: config.oldAmount,
            paymentMethod: config.paymentMethod,
            get selectedTask() {
                return this.tasks.find((task) => String(task.id) === String(this.selectedTaskId));
            },
            init() {
                if (!this.paymentAmount && this.selectedTask) {
                    this.useRemaining();
                }
                lucide.createIcons();
            },
            money(value) {
                return `\u20b1${Number(value || 0).toFixed(2)}`;
            },
            needsReference() {
                return this.paymentMethod !== 'Cash';
            },
            paymentValue() {
                const amount = Number(this.paymentAmount || 0);
                return this.selectedTask ? Math.min(Math.max(amount, 0), this.selectedTask.remaining) : amount;
            },
            useDownpayment() {
                if (!this.selectedTask) return;
                this.paymentAmount = Math.max(this.selectedTask.remaining * 0.5, 0).toFixed(2);
            },
            useRemaining() {
                if (!this.selectedTask) return;
                this.paymentAmount = Number(this.selectedTask.remaining || 0).toFixed(2);
            },
            newPaid() {
                return this.selectedTask ? this.selectedTask.paid + this.paymentValue() : 0;
            },
            newBalance() {
                return this.selectedTask ? Math.max(this.selectedTask.amount - this.newPaid(), 0) : 0;
            },
            newProgress() {
                if (!this.selectedTask || this.selectedTask.amount <= 0) return 0;
                return Math.min(100, Math.round((this.newPaid() / this.selectedTask.amount) * 100));
            },
        };
    }
</script>
@endsection
